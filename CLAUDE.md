# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## プロジェクト概要

Novalumo 社内向けの業務管理ツール（発注書・出張申請・出張旅費精算・案件管理・顧客管理）。Laravel 8 + Blade + Bootstrap 5 のサーバーサイドレンダリング構成で、一部に React/TypeScript を後付けしている。UI・コード内コメントは日本語。

## 開発環境 (nix flake)

`flake.nix` + `.envrc` (`use flake`) で、Sail と同じバージョンのツールがホストに入る。direnv 済みならディレクトリに入るだけ、そうでなければ `nix develop`。

| ツール | バージョン | 由来 |
| --- | --- | --- |
| php | 7.4.33 | `nixpkgs-2205` |
| composer | 2.3.5 | `nixpkgs-2205` (php74 用) |
| node | 16.17.1 | `nixpkgs-2205` |
| yarn | 1.22.18 | `nixpkgs-2205` |

**なぜ nixpkgs input が 2 つあるか**: `php74` は nixpkgs 22.11 で削除されており（"php74 has been dropped due to the lack of maintanence from upstream"）、`nixpkgs-unstable` には php82 以降しか無い。本番・Sail・CI が PHP 7.4 なので、`nixpkgs-2205` (nixos-22.05) を別 input として pin している。**単一 nixpkgs にまとめようとすると PHP 7.4 が失われる**ので注意。Node 16 も同様の理由（`bcrypt` のネイティブビルドと、deploy.yml が想定する 16.x）で同じ input から取っている。

devShell が担うのはホスト側ツールチェーンのみ。**アプリの実行と MySQL は従来通り Sail (Docker)**。`shellHook` で `vendor/bin` と `node_modules/.bin` に PATH を通してある。

php74 はデフォルトで `gd` / `pdo_mysql` / `pdo_sqlite` / `mbstring` / `iconv` / `curl` / `zip` / `bcmath` / `exif` が有効で、`composer check-platform-reqs` は全項目 success。TCPDF の PDF 生成・freee API の cURL・CI と同じ sqlite テストまで追加設定なしで動く。

この devShell がある場合、Docker 越しに composer を回す `./runner composer:init` は不要で、`composer install` を直接叩ける。

## 開発コマンド

すべて Laravel Sail (Docker) 前提。`./runner` がラッパー。

```bash
./runner init          # .env 作成 + sail up
./runner up            # sail up
./runner down
./runner artisan <cmd> # sail artisan
./runner composer <cmd>
./runner yarn <cmd>
./runner db:reset      # migrate:reset → migrate → db:seed
./runner test          # PHP + JS 両方
./runner test:php      # sail test (PHPUnit)
./runner test:js       # yarn test (jest)
```

vendor が無い状態からの初回セットアップは `./runner composer:init`（ホストの Docker で `composer install --ignore-platform-reqs`）。

### テスト単体実行

```bash
./runner test:php --filter <TestName>
./vendor/bin/sail test tests/Feature/ExampleTest.php
./vendor/bin/phpunit --testsuite Unit   # sail 無しの場合
```

CI は `DB_CONNECTION=sqlite` を env で渡している（`phpunit.xml` 内の該当行はコメントアウト済み）。ローカルでは MySQL に接続するため、DB を触るテストを書く場合は接続先に注意。

### フロントエンドビルド

Laravel Mix (webpack)。パッケージマネージャは **yarn**（`yarn.lock` のみ存在）。

```bash
./runner yarn watch    # 開発時
./runner yarn prod     # 本番ビルド（デプロイでも実行される）
```

`resources/ts/app.tsx` → `public/js/app.js`、`resources/sass/app.scss` → `public/css/app.css`。`mix.version()` 有効なので `public/mix-manifest.json` も更新される。

## PHP バージョンの注意

`composer.json` は `^7.3|^8.0` だが、実際に動いているのは **PHP 7.4**（`docker-compose.yml` は `docker/7.4`、CI も `php-version: "7.4"`）。`docker/8.0` `docker/8.1` のイメージ定義はあるが未使用。PHP 8 専用構文は使わないこと。

## アーキテクチャ上の重要な癖

### 認証は Laravel Auth を使っていない

`Illuminate\Auth` ではなく **素のセッション**で実装されている。

- `LoginController::auth()` がパスワード照合後、`session(['user_id', 'name', 'email'])` を手動でセット
- `App\Http\Middleware\LoginMiddleware`（ルートミドルウェア名 `login`）が `session('name') === null` で `/login` にリダイレクト
- 認証が必要なルートは `Route::middleware('login')->group(...)` で囲む（`auth` ミドルウェアではない）
- ログインユーザー参照は `session('user_id')` / `session('name')`。`Auth::user()` は機能しない
- 代替ログイン: NFC（`auth_with_nfc` / Web NFC API）、MetaMask（`auth_with_metamask` / ウォレットアドレス照合）
- 2FA は未実装（`UserController::register_2fa_auth` はスタブ、`users.two_factor_secret_code` カラムのみ存在）

### コントローラの GET/POST 兼用パターン

`create` / `edit` は 1 メソッドでフォーム表示と保存を兼ねる。ルート側で同じメソッドに GET と POST の両方を登録し、メソッド内で `$request->isMethod('POST')` 分岐する。新しい CRUD を足すときはこの形に合わせる。

```php
Route::get('/create', 'create')->name('xxx.create');
Route::post('/create', 'create');
```

### フラッシュメッセージ

リダイレクト時は必ずこの 3 キーをセットする。`resources/views/layouts/default.blade.php` が Bootstrap toast として描画する。

```php
return redirect('/orders')->with([
    'flash_message' => '...',
    'flash_status'  => 'success', // Bootstrap のカラー名
    'flash_icon'    => 'check-circle-fill', // bootstrap-icons 名
]);
```

### Eloquent のリレーションは定義されていない

モデルは `$fillable` のみで `hasMany` / `belongsTo` を持たない。関連取得はコントローラ内でクエリビルダの `join` か、個別 `find()` で行っている（例: `OrderController::pdf()`）。

- 発注書は `order_headers` (1) : `order_details` (N)。外部キーは `order_details.slip_id` → `order_headers.id`（`order_header_id` ではない）
- 編集時は「既存明細を全削除 → 再作成」方式（`OrderController::edit()`）

### 論理削除は手動

Laravel の `SoftDeletes` は使わず、`order_headers.is_deleted` (integer) を自前で見ている。一覧は `where('is_deleted', '!=', '1')`、`/orders/trash` がゴミ箱、`/orders/restore/{id}` が復元。

### PDF 生成 (TCPDF / FPDI)

2 方式が混在する。

- **ゼロから描画**: `OrderController::pdf()` — `setasign\Fpdi\Tcpdf\Fpdi` に座標指定で直接書き込む。ロゴ・社印は `resources/img/`
- **テンプレート PDF に重ね書き**: `TravelController::pdf()` / `TravelExpenseController::pdf()` — `resources/pdf/*.pdf` を `setSourceFile()` + `importPage()` で読み込み、その上にテキストを配置

日本語フォントは `kozminproregular`（明朝）/ `kozgopromedium`（ゴシック）。座標は mm 単位のマジックナンバーなので、レイアウト変更時は実際に PDF を出して確認すること。

`PrintController` は CakePHP からの移植途中で**動作しない**（`$this->Clients` / `$this->RequestHandler` / `WWW_ROOT` は Laravel に存在しない）。ルートにも未登録。

### 外部連携

- **freee API** (`freeeController`): SDK を使わず生の cURL。トークン類は `.env` の `FREEE_API_*` から `env()` で直読み（config 経由ではない）
- **Google Sheets** (`App\Models\SpreadSheet`): `resources/json/credentials.json`（gitignore 済み、`credentials.example.json` が雛形）+ `.env` の `GOOGLE_SPREADSHEET_ID`。Eloquent モデルではなく static ユーティリティとして使われている
- **CSV インポート**: `SplFileObject` + `READ_CSV` で読み、`header` パラメータが真なら 1 行目を捨てる（`TravelController` / `TravelExpenseController` の `csvImport`）

### フロントエンドの二重構造

主体は Blade + Bootstrap 5 + jQuery。React は「特定 DOM に自己マウントする部品」として同居している。

- 各コンポーネントがファイル末尾で `document.getElementById(...)` を見て自分で `ReactDOM.render` する（例: `Calc.tsx`、`ProjectsModal.tsx` → Blade 側の `<div id="projects-modal">`）
- `app.tsx` は react-router の `<App />` を `#app` にマウントするが、`#app` は `layouts/default.blade.php` 内にあるため全ページに存在する
- `resources/ts/lib/novalumo.ts` は `window.novalumo` として公開され、Blade の inline スクリプトから呼ばれる
- **Inertia は未使用**: `inertiajs/inertia-laravel` と `HandleInertiaRequests`（web ミドルウェアに登録済み）は入っているが、ルートビュー `app.blade.php` が存在せず Inertia レスポンスを返す箇所も無い
- React 17（`ReactDOM.render`）。`@types/react` は 18 系で型がずれることがある

## デプロイ

`.github/workflows/deploy.yml`: `main` への push で PHPUnit（sqlite）→ SSH でサーバに接続し `git fetch origin main && git reset --hard origin/main` → `yarn install && yarn prod`。

注意点:

- デプロイスクリプトは **`composer install` を実行しない**。PHP 依存を追加した場合はサーバ側で手動対応が必要
- **マイグレーションも自動実行されない**。`./runner prod:migrate` は `migrate:fresh`（＝全テーブル削除）なので本番では絶対に使わないこと
- Node.js のテストジョブはコメントアウトされている（jest のテストファイル自体が未作成）

## コーディング規約

- StyleCI: `laravel` preset（`no_unused_imports` は無効化）
- インデント: PHP/その他 4 スペース、JS/TS/JSX/YAML 2 スペース（`.editorconfig`）
- コミットメッセージは `add:` / `feat:` / `fix:` などの小文字プレフィックス（Git hook による自動付与は無し）
