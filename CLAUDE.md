# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## プロジェクト概要

Novalumo 社内向けの業務管理ツール（発注書・出張申請・出張旅費精算・案件管理・顧客管理）。Laravel 9 + Blade + Bootstrap 5 のサーバーサイドレンダリング構成で、一部に React/TypeScript を後付けしている。UI・コード内コメントは日本語。

**Laravel 8 から 13 へ、メジャーバージョンを 1 つずつ上げている途中**（1 メジャー = 1 PR）。現在 9。

## 開発環境 (nix flake)

`flake.nix` + `.envrc` (`use flake`) で、Sail と同じバージョンのツールがホストに入る。direnv 済みならディレクトリに入るだけ、そうでなければ `nix develop`。

| ツール | バージョン | 由来 |
| --- | --- | --- |
| php | 8.1.19 | `nixpkgs-2211` |
| composer | 2.5.4 | `nixpkgs-2211` (php81 用) |
| node | 16.19.1 | `nixpkgs-2211` |
| yarn | 1.22.19 | `nixpkgs-2211` |

**なぜ nixpkgs input が 2 つあるか**: `nixpkgs-unstable` には php82 以降しか無く、`php81` は EOL 扱いで評価が throw される。Laravel 9 の要件は PHP 8.0.2+ なので、`nixpkgs-2211` (nixos-22.11) を別 input として pin して php81 を引いている。**単一 nixpkgs にまとめようとすると PHP 8.1 が失われる**ので注意。Node 16 も同じ input から取っている。

Laravel 10 以降は PHP 8.1+ / 8.2+ が要件になり、Node も 18+ が要るため、アップグレードのたびにこの input を張り替える。

devShell が担うのはホスト側ツールチェーンのみ。**アプリの実行と MySQL は従来通り Sail (Docker)**。`shellHook` で `vendor/bin` と `node_modules/.bin` に PATH を通してある。

php81 はデフォルトで `gd` / `pdo_mysql` / `pdo_sqlite` / `mbstring` / `iconv` / `curl` / `zip` / `bcmath` / `exif` が有効で、追加設定なしで TCPDF の PDF 生成・freee API の cURL・sqlite でのテストまで動く。

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

`phpunit.xml` で `DB_CONNECTION=sqlite` / `DB_DATABASE=:memory:` を指定しているため、テストは MySQL を必要とせず Sail を起動しなくても回る。DB を使うテストは `RefreshDatabase` を付ける。

**sqlite と MySQL の型差異に注意**: sqlite (PDO) は integer カラムを文字列で返す。`$header->total_price` は `'1100'` であって `1100` ではないため、テストで数値比較する際は `(int)` にキャストする。この差異は `ProjectController::index()` の挙動まで変える（後述）。

### フロントエンドビルド

**Vite**（Laravel 9.19+ の推奨。Laravel Mix から移行済み）。パッケージマネージャは **yarn**（`yarn.lock` のみ存在）。

```bash
./runner yarn dev      # 開発サーバ (HMR)
./runner yarn build    # 本番ビルド
```

エントリは `vite.config.js` の `input` に定義（`resources/sass/app.scss` と `resources/ts/app.tsx`）。出力は `public/build/`（gitignore 済み）で、`manifest.json` を Blade の `@vite` が読む。

Blade 側は `layouts/default.blade.php` と `layouts/auth.blade.php` で
`@viteReactRefresh` → `@vite([...])` の順に書く（React Refresh は `@vite` より前でないと動かない）。

**テストでは `withoutVite()` が必須**。`tests/TestCase.php` の `setUp()` で呼んでいる。これがないと `@vite` がビルド成果物を探しに行き、テスト前に `yarn build` が必要になる。

Vite は ESM 前提なので `require()` は使えない。`jquery-ui` は `window.jQuery` を参照するため、`resources/ts/lib/jquery/setup.ts` で先にグローバルを用意してから読み込んでいる（import は宣言順に評価される性質を利用）。

## PHP バージョンの注意

**PHP 8.1**（`composer.json` は `^8.0.2`、`docker-compose.yml` は `docker/8.1` を参照、nix devShell も 8.1.19）。`docker/7.4` `docker/8.0` のイメージ定義は残っているが未使用。

PHP 8.1 で **PDO SQLite が integer / float を native type で返すようになった**（7.4 までは文字列）。テストは sqlite、本番は MySQL なので、型に依存するコードは両者で挙動が変わりうる。実際 `ProjectController::index()` のステータス表示はこの影響を受けている（後述）。

## 既知の不具合（アップグレード前から壊れている）

リグレッションテスト追加時に判明したもの。**Laravel のバージョンを上げて壊れたのではなく、元から壊れている**。該当テストは `markTestIncomplete()` で理由付きで残してあるので、直したら外すこと。

- **発注書の編集が保存できない**: `OrderController::edit()` は `$req['is_issued']` / `is_ordered` / `is_deleted` / `is_converted` を参照するが、`resources/views/orders/form.blade.php` に対応する入力が一切ない。POST すると `Undefined index` で 500 になる
- **`OrderController::view()` が存在しない**: ルート `orders.view` (`/orders/view/{id}`) は登録されているのにメソッドが無く 500。一覧にリンクが無いため UI からは到達しない
- **顧客の編集が保存されない**: `CustomerController::edit()` に `isMethod('post')` の分岐が無く、POST しても常に view を返すだけ
- **案件のステータス表示が壊れている**: `ProjectController::index()` が `switch ($project->status) { case $project->status === 0: ... }` と書かれている（`switch (true)` の誤用）。sqlite では status 1・2 が「未知」になる。値の型が変わる MySQL では結果が変わりうる
- **明細のない発注書は CSV 出力できない**: `OrderController::csv()` が `$details[0]` を無条件に参照する
- **`OrderController::csv()` はカレントディレクトリにファイルを書く**: `'./' . $order_no . '.csv'` を作って `readfile()` 後に `unlink()` する。`order_no` は検証されていない

### テストしにくい箇所

- `OrderController::setStatus()` は `file_get_contents("php://input")` を直接読むため、Laravel のテストからは検証できない
- `OrderController::csv()` は Laravel の Response ではなく素の `header()` + `readfile()` で出力するため、テスト実行中は "headers already sent" になる。PDF (`$pdf->Output()`) は出力バッファで捕捉できる

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
- **Inertia は未使用**: composer 側の `inertiajs/inertia-laravel` と `HandleInertiaRequests`（web ミドルウェアに登録済み）は残っているが、ルートビュー `app.blade.php` が存在せず Inertia レスポンスを返す箇所も無い。npm 側の `@inertiajs/*` は Vite 移行時に削除済み
- React 17（`ReactDOM.render`）。`@types/react` は 18 系で型がずれることがある

## デプロイ / CI

**現在 CI・デプロイのワークフローは無い**。`.github/workflows/deploy.yml` は、デプロイ先サーバーが廃止され `main` への push のたびに SSH 接続で失敗する状態になっていたため一旦削除した。同ファイルに同居していた `php-tests`（PHPUnit）ジョブも同時に失われている。

作り直す場合、旧ワークフローが抱えていた問題を引き継がないよう注意する。

- 旧デプロイは SSH 先で `git reset --hard origin/main` → `yarn install && yarn prod` を実行するだけで、**`composer install` を実行しなかった**
- **マイグレーションも自動実行されなかった**。`./runner prod:migrate` は `migrate:fresh`（＝全テーブル削除）なので本番では絶対に使わないこと
- Node.js のテストジョブはコメントアウトされていた（jest のテストファイル自体が未作成）

## コーディング規約

- StyleCI: `laravel` preset（`no_unused_imports` は無効化）
- インデント: PHP/その他 4 スペース、JS/TS/JSX/YAML 2 スペース（`.editorconfig`）
- コミットメッセージは `add:` / `feat:` / `fix:` などの小文字プレフィックス（Git hook による自動付与は無し）
