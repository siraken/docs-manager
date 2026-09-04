# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## プロジェクト概要

Novalumo 社内向けの業務管理ツール（発注書・出張申請・出張旅費精算・案件管理・顧客管理・勤務報告・契約管理）。Laravel 13 + Tailwind CSS v4。UI・コード内コメントは日本語。

**画面は Blade から Inertia + Svelte 5 へ移行済み**（全 28 画面）。Blade として残っているのは Inertia のルートテンプレート (`app.blade.php`)、エラーページ (`errors/`)、メール本文 (`emails/`) だけ。Alpine.js と `resources/views/components/` は削除済み。詳しくは「フロントエンドの構成」を参照。

Laravel 8 から 13 へ、メジャーバージョンを 1 つずつ上げてきた（1 メジャー = 1 PR）。**現在 13 で、アップグレードは完了している。**

サーバー側は **Laravel の既定 (`app/Http`) の内側に Domain / Application / Infrastructure を足した**構成（「レイヤー構成」を参照）。以前はコントローラに DB アクセス・金額計算・PDF 描画・外部 API 呼び出しが直書きされていた。

## 開発環境 (nix flake)

`flake.nix` + `.envrc` (`use flake`) で、Sail と同じバージョンのツールがホストに入る。direnv 済みならディレクトリに入るだけ、そうでなければ `nix develop`。

| ツール | バージョン |
| --- | --- |
| php | 8.3.33 |
| composer | 2.10.2 |
| node | 22.23.2 |
| pnpm | 11.22.0 |
| just | 1.58.0 |

すべて `nixpkgs-unstable` の単一 input から取っている。

**input が 1 つに戻っている経緯**: Laravel 10 までは PHP 8.1 が要件で、`php81` が unstable では EOL 扱いで評価が throw されるため `nixos-22.11` を別 input として pin していた。Laravel 11 で要件が PHP 8.2+ になり、unstable の `php82` で満たせるようになったのでその input を削除した。

Node 22 なのは、pnpm 11 が Node 22.13+ を、Vite 8 が Node 20.19+ / 22.12+ を要求するため（`nodejs_18` / `nodejs_20` は unstable では EOL 扱いで引けない）。

devShell が担うのはホスト側ツールチェーンのみ。**アプリの実行と MySQL は従来通り Sail (Docker)**。`shellHook` で `vendor/bin` と `node_modules/.bin` に PATH を通してある。

php83 はデフォルトで `gd` / `pdo_mysql` / `pdo_sqlite` / `mbstring` / `iconv` / `curl` / `zip` / `bcmath` / `exif` が有効で、追加設定なしで TCPDF の PDF 生成・freee API の cURL・sqlite でのテストまで動く。

この devShell がある場合、Docker 越しに composer を回す `just composer-init` は不要で、`composer install`（または `just install`）を直接叩ける。

## 開発コマンド

**`just`**（レシピは `justfile`）。引数なしで叩くとレシピ一覧が出る。以前は `runner` という bash スクリプトだったが、just に置き換えて削除した。

**Docker (Sail) が要るのはアプリサーバーと MySQL だけ**。テストもビルドも型チェックも devShell のツールでホストのまま回る。Sail 越しに動かしたいものには `sail-` を頭に付けたレシピを用意してある。

```bash
# ホストで動く (devShell)
just test [args]       # ./vendor/bin/pest
just tsc               # tsc --noEmit (.ts)
just svelte-check      # svelte-check (.svelte)
just check             # test → tsc → svelte-check
just dev               # pnpm dev (HMR)
just build             # pnpm build
just artisan <cmd>     # php artisan
just composer <cmd>
just pnpm <cmd>

# Docker (Laravel Sail)
just up                # sail up
just down              # sail down
just sail-build [args]
just sail-artisan <cmd>
just sail-composer <cmd>
just sail-pnpm <cmd>
just sail-npx <cmd>
just sail-test [args]  # MySQL でテストを回したいとき
just db-reset          # migrate:reset → migrate → db:seed

# セットアップ
just init              # .env 作成 + APP_KEY 発行
just install           # composer install + pnpm install
just composer-init     # devShell が使えないときの退避路 (Docker 内の composer)
```

**`just dev` は Vite しか起動しない**。アセット配信と HMR だけなので、画面を見るには別のターミナルで `just up`（Sail のアプリ + MySQL）が要る。開くのは **`http://localhost`**（`docker-compose.yml` の `APP_PORT` 既定値 80）であって、Vite が起動時に表示する `http://localhost:5173` ではない。後者はアセット用で、ルートを開いても何も返さない。

```bash
# ターミナル 1
just up            # アプリ + MySQL (http://localhost)
# ターミナル 2
just dev           # Vite の HMR
```

`just dev` を動かすと `public/hot` が作られ、`app.blade.php` の `@vite` がアセットの参照先を Vite の dev サーバーに切り替える。ページのオリジン（`http://localhost`）と Vite のオリジン（`http://127.0.0.1:5173`）は別になるが、`laravel-vite-plugin` が CORS を通すので問題なく読める。**Vite を止めたら `public/hot` が消えることを確認すること**（残っているとビルド成果物ではなく止まった dev サーバーを見にいくため、画面が真っ白になる）。

`set positional-arguments` を使い、レシピ側では `"$@"` で受けている。そのため `just artisan make:model "My Model"` のように**空白を含む引数もそのまま渡せる**（旧 `runner` は `ARGS=${@:2}` を単語分割される形で展開していたため、空白入りの引数が分裂した）。

**`prod:migrate` は移していない**。`ssh` 先で `migrate:fresh`（＝全テーブル削除）を走らせるうえ、接続先のサーバーは廃止済みだった（「デプロイ / CI」を参照）。

### テスト

**Pest 4**。`artisan test` も Pest を使う。

```bash
./vendor/bin/pest                              # 全件
./vendor/bin/pest tests/Feature/AuthTest.php   # ファイル指定
./vendor/bin/pest --filter 'ログイン'           # 名前で絞る
just test                                      # ホスト (sqlite)
just sail-test                                 # Sail 経由 (MySQL)
```

テストは Pest の関数記法（`test()` / `beforeEach()` / `expect()`）で書く。

**共通のフィクスチャは `tests/Pest.php` に置く**。Pest ではテストファイル内で定義した関数もグローバルスコープに入るため、複数ファイルで同名の関数を定義すると再宣言エラーになる。`createUser()` / `actingAsUser()` / `createCustomer()` / `createHeader()` / `orderPayload()` / `walletAddress()` がここにある。**この制約は Unit テストにも及ぶ**ので、テストファイル内でヘルパを定義するときは他ファイルと衝突しない名前にすること（例: `tests/Unit/OrderTest.php` の `newOrder()` / `orderLine()`）。

フィクスチャは Eloquent モデルを直接使う。ユースケース経由で用意すると「準備」と「検証対象」が同じ経路になり、リグレッションを検出できなくなる。

`uses(TestCase::class)->in('Feature')` と `uses(RefreshDatabase::class)->in('Feature')` も `tests/Pest.php` で設定している。

**画面のテストは `viewData()` ではなく props を見る**。`viewData()` は Blade のビューにしか使えず、画面はすべて Inertia になっている。

```php
$this->get('/orders')->assertInertia(fn (AssertableInertia $page) => $page
    ->component('Orders/Index')
    ->has('orders', 1)
    ->where('orders.0.orderNo', 'ALIVE-1'));
```

`config/inertia.php` で `ensure_pages_exist` を有効にしてあるので、`component()` はページの実在も確認する（`resources/ts/Pages` を探す）。ページ名の打ち間違いがテストで落ちる。

**ドメイン層は Laravel に依存しないので Unit テストで書ける**。`tests/Unit/` にある Money / TaxRate / ProjectStatus / OrderNo / TwoFactorSecret / Order / TravelExpense のテストはフレームワークを起動せずに動く。金額計算やステータス遷移のような業務ルールはここに書き、HTTP の配線は Feature に書く。

**PHPUnit 12**（12.5.33）。`phpunit.xml` で `DB_CONNECTION=sqlite` / `DB_DATABASE=:memory:` を指定しているため、テストは MySQL を必要とせず Sail を起動しなくても回る。DB を使うテストは `RefreshDatabase` を付ける。設定は PHPUnit 10 で入った形式（`<coverage>` ではなく `<source>`）のままで 12 でもそのまま通る。

`phpunit.xml` の `xsi:noNamespaceSchemaLocation` は `10.5` を指したままだが、**PHPUnit 12 はこれを警告しない**（スキーマの参照先はエディタ向けで、PHPUnit 自身は使わない）。動かないわけではないので、慌てて直さなくてよい。

**sqlite と MySQL の型差異に注意**: sqlite (PDO) は integer カラムを文字列で返す。`$header->total_price` は `'1100'` であって `1100` ではないため、テストで数値比較する際は `(int)` にキャストする。アプリ側では Mapper（`app/Infrastructure/Persistence/Eloquent/Mapper/`）がドメインへ移す際に必ず型を寄せているので、この差異がドメイン層まで漏れることはない。

### フロントエンドビルド

**Vite 8**（Laravel Mix から移行済み）。パッケージマネージャは **pnpm**（`pnpm-lock.yaml`）。TypeScript は **6.0**。

**Vite 8 は束ね役が Rollup + esbuild から Rolldown + Oxc に変わっている**。`build.rollupOptions` は `build.rolldownOptions` に、`esbuild` 設定は `oxc` に改名された（このプロジェクトはどちらも使っていない）。`esbuild` は Vite の optional な peer に降格し、**依存から完全に消えた**。

**バンドル対象に `.js` は 1 つも無い**。`tsconfig.json` の `include` は `resources/ts/**/*` と `vite.config.ts`。**`tsc --noEmit` も `svelte-check` も現在 0 件で通る**ので、型エラーを増やしたまま放置しないこと（`just check` で両方走る）。

```bash
just dev       # 開発サーバ (HMR)
just build     # 本番ビルド
```

**pnpm 10 以降は依存パッケージの postinstall を既定でブロックする**（サプライチェーン対策）。許可は `pnpm-workspace.yaml` の `allowBuilds` に書く。値はリストではなく「パッケージ名 → bool」のマップである点に注意。設定を足すときは `pnpm approve-builds <pkg> '!<pkg>'` を使うと正しい書式で書き込まれる。**現在、許可が要るパッケージは 1 つも無い**（Vite 8 で esbuild が依存から消え、Rolldown はプリビルドを optional dependency として配るため postinstall を必要としない）。

**TypeScript 6 は `moduleResolution: "node"` (node10) を非推奨エラーにする**。TS 7 で機能停止するため、`tsconfig.json` は `module: "esnext"` + `moduleResolution: "bundler"` に移行済み。`import.meta.env` の型は `types` に `vite/client` を足して解決している（無いと `ImportMeta` に `env` が生えず `nfc-auth.ts` / `metamask-auth.ts` が型エラーになる）。なお **tsc は emit に使っていない**（`--noEmit` のみ）。実際のトランスパイルはバンドラ側（Vite 8 では Oxc、それ以前は esbuild）が行う。

エントリは `vite.config.ts` の `input` に定義（`resources/css/app.css` と `resources/ts/app.ts`）。出力は `public/build/`（gitignore 済み）で、`manifest.json` を `app.blade.php` の `@vite` が読む。

**ネイティブバイナリを使う依存が 2 つある**: Tailwind v4 の `@tailwindcss/oxide` と、Vite 8 のバンドラである `rolldown`。どちらもプラットフォーム別のプリビルドを optional dependency として配り（`@tailwindcss/oxide-darwin-arm64` / `@rolldown/binding-darwin-arm64`）、`engines` に Node のバージョン制約を持つ。**ホストの Node が古いまま `pnpm install` すると engines 不一致で黙ってスキップされ**、ビルド時に `Cannot find native binding` で落ちる。`node_modules` を消して **devShell の中で** 入れ直すこと。

`@vite([...])` を書くのは `resources/views/app.blade.php` だけ。**`@viteReactRefresh` は削除済み**（React を剥がしたため）。

**テストでは `withoutVite()` が必須**。`tests/TestCase.php` の `setUp()` で呼んでいる。これがないと `@vite` がビルド成果物を探しに行き、テスト前に `pnpm build` が必要になる。

Vite は ESM 前提なので `require()` は使えない。バンドル対象の JS/TS は全て ESM で書く。

### Svelte

**Svelte 5**（runes）。**SvelteKit は使っていない** —— ルーティングは Laravel が持ち、Inertia がページを差し替える（「フロントエンドの構成」を参照）。ページ全体が Svelte で、Blade の DOM に差し込む「島」は無い。

- ビルドは `@sveltejs/vite-plugin-svelte` 7.x。**バージョンを上げるときは Vite との対応に注意**: peer が `vite ^8` で、`laravel-vite-plugin` 3.x も同じく Vite 8 を要求する。この 3 つは足並みを揃えて上げること
- `svelte.config.js` は `vitePreprocess()` だけ。`<script lang="ts">` はこれを通して Vite（8 では Oxc）が処理する
- **`tsc` は `.svelte` の中身を見ない**。型を担保するのは `svelte-check` なので、`just check` は両方走らせる。`resources/ts/types/svelte.d.ts` の `declare module "*.svelte"` は「import できること」を tsc に教えるだけのもの（SvelteKit を使っていないと降ってこないため自前で置いている）

### 発注書の明細テーブル (OrderLines.svelte)

金額計算・行の追加/削除・ドラッグでの並べ替えは `resources/ts/components/OrderLines.svelte` が担当する。

移行の経緯: jQuery + jquery-ui → 素の DOM API (`lib/order-form.ts`) → Svelte。DOM を走査していた頃は行が状態として存在せず、追加は `<template>` の複製、並べ替えは DOM の付け替えで表現していた。いまは行が配列なので、どれも配列操作になる。

- **行の型は `lib/order-line.ts` の `OrderLineDraft`**。数量も単価も文字列で持つ（input の値がそのまま入るため、"1," のような途中の入力を保持できる必要がある）
- **表示している金額は画面のためだけのもの**。保存される金額は `Domain\Order\Entity\OrderLine` が数量・単価・税区分から計算し直す（フォームは金額を送らない）。`OrderLines.svelte` の `TAX_RATES` は表示用の写しで、正は `Domain\Order\ValueObject\TaxRate`
- **`draggable` は掴む直前に立てる**。`pointerdown` の位置が入力欄なら立てない。常時 `true` にすると入力欄の文字選択がドラッグに横取りされる（jquery-ui の `cancel` 既定と同じ考え方）
- `dragover` で `preventDefault()` を呼ばないとドロップ先として認識されない。Firefox は `dataTransfer` に何か入れないとドラッグ自体が始まらない

### スタイル (Tailwind CSS v4)

Bootstrap 5 は削除済み。**Tailwind CSS v4** の CSS-first 構成で、`tailwind.config.js` も PostCSS も autoprefixer も無い。

- Vite プラグイン `@tailwindcss/vite` を `vite.config.js` に足し、`resources/css/app.css` の先頭で `@import "tailwindcss"` する
- 配色などは同ファイルの `@theme` ブロックで CSS 変数として定義する。ブランドカラー `--color-brand-*` は、もともと独自 CSS のアクセントに使われていた `#00acc1` (Material Cyan 600) を基点にしたスケール
- 走査対象は `@source` で `../views` と `../ts` を明示している（v4 は既定でプロジェクト全体を走査するが、明示しておく）
- 独自 CSS は `resources/css/document-table.css`（発注書の明細テーブル）だけ。**SCSS は廃止**（ネストは Lightning CSS が素の CSS として処理する）ので `sass` 依存も外してある。品目候補のオーバーレイ用の `item-overlay.css` もあったが、開閉する JS が存在せず `display: none` のままだったので削除した

**アイコンは `bootstrap-icons`**（Bootstrap 本体とは別プロジェクト）。CDN ではなく npm 依存にして Vite にバンドルさせている。フラッシュメッセージの `flash_icon` にコントローラから `bi-` 名を渡す仕組みは従来どおり。CSS の大半（117KB 中 100KB 程度）はこのアイコン定義で、使うのは十数種だが CDN 時代と同じものなので絞り込んではいない。

### UI コンポーネント

再利用する UI は `resources/ts/components/ui/` にある。`Button` / `Input` / `Select` / `Textarea` / `Toggle` / `Label` / `Card` / `Table` / `Badge` / `EmptyState` / `PageHeader` / `Modal` / `Dropdown`（+ `DropdownItem` / `DropdownDivider`）/ `Flash` / `FormErrors` / `DetailList`。それ以外はユーティリティを直書きする。

**Bootstrap 5 の JS コンポーネント（modal / dropdown / collapse / toast）は、一度 Alpine.js に置き換えたあと Svelte に移した**。Alpine とその Blade 版コンポーネント (`resources/views/components/`) は Inertia 化の完了と同時に削除してある。

**フラッシュのトーストはヘッダーと重なる**。`Flash` は `top` プロパティで位置を受け取り、`Layouts/Default` では既定の `top-20`（h-16 のヘッダーの下）、`Layouts/Auth` では `top-4` を渡している。

### 画面に渡すのは ViewModel

**Eloquent モデルもドメインエンティティも画面に渡さない**。`app/Http/ViewModels/` の readonly クラス（`OrderView` / `CustomerView` / `ProjectView` / `UserView` / `TravelView` / `TravelExpenseView` / `CompanyProfileView` / `AcademyInquiryView`）に整形済みの値を詰めて渡す。

- プロパティはキャメルケース（`$row->issuedDateLabel`）。移行前はカラム名で `$row['issued_date']` と引いていたため、DB のカラム名を変えると画面が壊れた
- 日付や金額は**整形済みの値も持たせる**（`issuedDate` = `2026-09-01` / `issuedDateLabel` = `2026/09/01`、`total` = `1650` / `totalLabel` = `1,650`）。前者はフォームの `value`、後者は表示に使う
- 一覧には `::collection()` で `Illuminate\Support\Collection` を返す
- 新規作成フォームには `::empty()` を渡すか、props を `null` にして画面側で分岐する。既定値をサーバーが持つなら前者（`TravelExpenseView`）、持たないなら後者（`CustomerView` / `ProjectView` / `UserView`）

### ローカルでの動作確認 (sqlite)

MySQL を立てずに画面を確認したいときは sqlite に向けられる。ただし **`php artisan serve` は任意の環境変数を子プロセスに渡さない**（`ServeCommand` の passthrough 許可リストにあるものだけ）。`DB_CONNECTION=sqlite artisan serve` は効かず `.env` の MySQL を見にいくので、PHP 内蔵サーバーを直接叩くこと。

```bash
php -S 127.0.0.1:8123 -t public <ルーターPHP>   # DB_CONNECTION / DB_DATABASE を env で渡す
```

**ルーター PHP を省いて `public/index.php` を直接渡してはいけない**。ルータースクリプトを指定すると静的ファイルもそれを通るため、ビルド済みの JS/CSS まで Laravel に流れ、`login` ミドルウェアが 302 を返す。HTML は普通に返るので curl での確認は通ってしまい、**ブラウザで開くと画面が真っ白**になる。ルーターは実ファイルがあるパスで `false` を返して内蔵サーバーに配信させること。

```php
$root = __DIR__ . '/public';
$path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($path !== '/' && is_file($root . $path)) {
    return false;   // 静的ファイルは内蔵サーバーに任せる
}

require_once $root . '/index.php';
```

`config:cache` していなければ、実プロセスの環境変数は `.env` より優先される（Laravel は `Dotenv::createImmutable` を使う）。

## PHP バージョンの注意

**PHP 8.3**（`composer.json` は `^8.3`、nix devShell は 8.3.33）。

Sail のイメージは **`vendor/laravel/sail/runtimes/8.3` を直接参照**している（`docker-compose.yml` の `context` と `image` がどちらも 8.3）。以前はリポジトリ内の `docker/{7.4,8.0,8.1}` に Sail のコピーを抱えていたが、Ubuntu 21.10（EOL）ベースで独自カスタマイズも無かったため、Laravel 11 化の際に削除して公式の runtime に委譲した。PHP を上げるときは `docker-compose.yml` の `context` と `image` のバージョンを変えるだけでよい（vendor 内には 8.0〜8.5 が揃っている）。

`vendor/` は gitignore されているので、`sail up` の前に `composer install` が必要。

PHP 8.1 で **PDO SQLite が integer / float を native type で返すようになった**（7.4 までは文字列）。テストは sqlite、本番は MySQL なので、型に依存するコードは両者で挙動が変わりうる。実際 `ProjectController::index()` のステータス表示はこの影響を受けている（後述）。

## アプリケーション構造 (Laravel 11+ の新形式)

Laravel 11 で導入された skeleton に合わせてある。**`app/Http/Kernel.php` や `app/Console/Kernel.php` は存在しない。**

- **ミドルウェアの登録は `bootstrap/app.php`**。`withMiddleware()` の中で、web グループへの `AddResponseHeaders` の append、`login` エイリアス (`LoginMiddleware`) の登録、api の `throttleApi()` を行う
- **`throttleApi()` を外さないこと**。Laravel 11 以降、api グループの既定は `SubstituteBindings` だけで `throttle:api` はオプトインに変わった。旧 `app/Http/Kernel.php` では有効だったので明示的に復元してある。参照される `api` リミッター (60/min) は `AppServiceProvider::boot()` にある
- **ルーティングも `bootstrap/app.php`** の `withRouting(web:, api:, commands:)`。旧 `RouteServiceProvider` は無い
- **例外ハンドリングは `withExceptions()`**。旧 `app/Exceptions/Handler.php` は無い
- **サービスプロバイダの登録は `bootstrap/providers.php`**。`config/app.php` に `providers` 配列は無い。`AppServiceProvider`（api の RateLimiter 定義）と `DomainServiceProvider`（インターフェースと実装の対応）の 2 つ
- **フレームワーク標準のミドルウェアはファイルとして持たない**。`TrustProxies` / `TrimStrings` / `EncryptCookies` などはすべて標準値のままだったので削除した。除外設定を足したくなったら `bootstrap/app.php` の `withMiddleware()` で行う（例: `$middleware->validateCsrfTokens(except: [...])`）
- **残しているカスタムミドルウェアは 2 つだけ**: `LoginMiddleware`（独自セッション認証）と `AddResponseHeaders`（`Server` ヘッダ）。どちらも Laravel の既定どおり `app/Http/Middleware/` にある
- **`AddResponseHeaders` は `$response->headers->set()` を使う**。`$response->header()` は `Illuminate\Http\Response` のメソッドで、ファイルダウンロードで返る `BinaryFileResponse` には無い。以前はこれが原因で `/downloader/{file}` が必ず 500 になっていた
- **例外の HTTP への変換は `bootstrap/app.php` の `withExceptions()`**。`EntityNotFoundException` を 404 に、それ以外の `DomainException` を「元の画面へリダイレクト + フラッシュ」（JSON リクエストなら 422）に落としている。ドメイン層がフレームワークを知らずに済むのはここで受けているため
- **翻訳ファイルは `lang/`**（`resources/lang/` ではない。Laravel 9 以降の配置）
- **config は必要なものだけ**。`cors` / `hashing` / `view` / `broadcasting` は全て標準値だったので削除し、フレームワークの既定に任せている
- **外部サービスの設定は `config/services.php`**。freee と Google スプレッドシートの認証情報をここに集約している。**`env()` を直接読まないこと**（`config:cache` した環境では `.env` を読み直さないため null になる）

## レイヤー構成

**HTTP まわりは Laravel の既定の場所に置き、その内側に 3 つの層を足す**という方針。`app/Http` を独自の場所（`app/Presentation` など）に動かすと、`artisan make:controller` の生成先とズレるうえ、Laravel を知っている人が最初に見る場所から外れる。**依存は外側から内側に向かう一方向**で、ドメイン層は Laravel も Eloquent も知らない。

```
app/
├── Http/             Laravel の既定。HTTP との変換だけを行う
│   ├── Controllers/      ユースケースを呼んで View か Response を返す
│   ├── Requests/         FormRequest。検証と Input DTO への組み替え
│   ├── ViewModels/       画面に渡す整形済みの値 (Inertia の props)
│   └── Middleware/
├── Support/Flash.php フラッシュメッセージの 3 キーを組み立てる
│
│   ここから下が拡張。Laravel の既定には無い
│
├── Domain/           業務ルール。フレームワーク非依存
│   ├── Shared/           Money、ドメイン例外
│   ├── Order/            Entity/Order・OrderLine、ValueObject、Repository インターフェース
│   └── Customer/ Project/ User/ Travel/ Academy/ Setting/
│       Contract/ Report/
├── Application/      ユースケース。「何をするか」の手順
│   ├── <文脈>/UseCase/   1 クラス 1 ユースケース (execute() だけを持つ)
│   ├── <文脈>/Input/     ユースケースへの入力 DTO
│   ├── <文脈>/Port/      外部に出ていく操作の抽象 (PDF 描画・CSV 読み書き・メール・セッション・外部 API)
│   └── Shared/           DateParser、RenderedDocument
└── Infrastructure/   技術的な詳細。Port と Repository の実装
    ├── Persistence/Eloquent/  Models・Mapper・各 Repository
    └── Pdf/ Csv/ Auth/ Mail/ Freee/ SpreadSheet/
```

**`app/Http` は「薄いこと」で層を保っている**。ディレクトリ名で守られていないぶん、ここに業務ロジックが戻ってこないかはレビューで見る必要がある。目安は「コントローラのメソッドが 10 行を超えたら、それはユースケース側の仕事」。

**新しい機能を足すときの流れ**:

1. 業務ルールがあるなら `Domain` にエンティティ / 値オブジェクトを置く（テストは `tests/Unit/`）
2. 手順を `Application/<文脈>/UseCase/` に 1 クラスで書く。必要な入力は `Input/` の DTO にする
3. DB や外部サービスに触るなら、まず `Domain/.../Repository/` か `Application/.../Port/` にインターフェースを置き、実装を `Infrastructure` に書く
4. `DomainServiceProvider::$bindings` に「インターフェース => 実装」を 1 行足す
5. `app/Http` に FormRequest とコントローラのメソッドを足し、`routes/web.php` に登録する

**守ること**:

- **ドメイン層に `use Illuminate\...` を書かない**。書きたくなったらそれは Infrastructure の関心
- **コントローラに業務ロジックを書かない**。分岐が出てきたらユースケース側へ。`app/Http` は Laravel の既定の場所なので、油断すると元の「全部入りコントローラ」に戻る
- **Eloquent モデルはリポジトリの外に出さない**。画面へはドメインのエンティティではなく ViewModel を渡す（画面が `is_issued` のようにカラム名で引くと、DB の都合が画面に漏れる）
- **ユースケースはコンストラクタでインターフェースを受け取る**。テストで `$this->app->instance(...)` して差し替えられる

**依存注入はメソッドインジェクションを使っている**。コントローラのコンストラクタに 10 個のユースケースを並べると、1 アクションのために全部が解決されてしまうため。

```php
public function index(ListOrdersUseCase $listOrders): View
{
    return view('orders.index', ['orders' => OrderView::collection($listOrders->execute())]);
}
```

### HTTP クライアント (ky)

**axios は廃止し `ky` を使う**（fetch のラッパー、依存 0）。設定済みインスタンスは `resources/ts/lib/http.ts` の `http` で、これを import すること。素の `ky` や `fetch` を直接使うと以下が漏れる。

- **`X-XSRF-TOKEN`**: `XSRF-TOKEN` クッキーの値を URL デコードして載せる。axios が暗黙にやっていた処理を `beforeRequest` フックで再現している。JSON を投げる先（`login-nfc` / `login-metamask` / `orders/set-status`）はすべて `routes/web.php` にあり CSRF の対象
- **`X-Requested-With: XMLHttpRequest`**

**ky 2.x のフックは引数を 1 つのオブジェクトで受け取る**（`({ request }) => ...`）。1.x の `(request, options) => ...` とは非互換なので、ネット上の 1.x 向けサンプルをそのまま貼らないこと。

axios との違いで注意が要るのは 2 点。

1. **既定タイムアウトが 10 秒**（axios は無制限）。長い処理を叩くときは `timeout` を明示する
2. **レスポンスボディは `.json()` で取り出す**。axios の `res.data` に相当するものは無い

リトライは既定で GET / PUT / HEAD / DELETE / OPTIONS / TRACE のみが対象で、**POST は再送されない**。現状の呼び出しはすべて POST なので axios と挙動は変わらない。

### フロントエンドの環境変数

Vite はビルド時に `import.meta.env.VITE_*` を値へ埋め込む。**`process.env.MIX_*` は解決されない**（Mix 時代の書き方が残っていると常に `undefined` になる）。

**いま `import.meta.env.VITE_*` を読むコードは無い**。`VITE_APP_ENV` は `nfc-auth.ts` / `metamask-auth.ts` がベースパス（`/docs-manager` プレフィックス）の判定に使っていたが、両ファイルは Inertia 化で削除された。URL はすべてサーバー側で組んでいるので、フロントがベースパスを知る必要そのものが無くなっている。`.env.example` の `VITE_APP_ENV` は残してあるが、現状どこからも読まれない。

`tsconfig.json` の `types` にある `vite/client` は引き続き要る。`app.ts` の `import.meta.glob` の型がこれで解決されるため。

## Laravel 13 で入れた設定

- **CSRF ミドルウェアは `PreventRequestForgery`**: Laravel 13 で `VerifyCsrfToken` からリネームされ、`Sec-Fetch-Site` ヘッダによるリクエスト元検証が加わった。**継承した独自クラスは持っておらず**、web グループにフレームワーク標準の `Illuminate\Foundation\Http\Middleware\PreventRequestForgery` がそのまま入っている（明示的に名前を書いているのは `config/sanctum.php` の `validate_csrf_token` だけ）。`VerifyCsrfToken` / `ValidateCsrfToken` は非推奨エイリアスとして残っているが使わないこと
- **`handle()` は `hasValidOrigin()` を `tokensMatch()` より先に評価する**。`Sec-Fetch-Site: same-origin` が付いていればトークンを見ずに通す。そのためブラウザからの同一オリジン fetch は CSRF トークンが無くても 200 になり、**ブラウザ操作だけではトークン検証の経路を確認できない**。検証したいときは `Sec-Fetch-Site` を送らない curl を使うこと（トークン無し・不正なら 419 になる）
- **`config/session.php` の `serialization` は `json`**: PHP の unserialize による gadget chain 攻撃を避けるため。このアプリはセッションに `user_id` / `name` / `email` の文字列しか入れていないので json で足りる
- **`config/cache.php` の `serializable_classes` は `false`**: キャッシュから PHP オブジェクトを復元しない設定。キャッシュにオブジェクトを入れていないため false のままでよい
- **`composer.json` の `allow-plugins` に `pestphp/pest-plugin`**: composer 2.2 以降はプラグインの実行に明示的な許可が要る。増やすときは必要最小限にする

## 移行時に直した不具合

レイヤー分割にあわせて、**アップグレード前から壊れていた箇所をまとめて直した**。以下はいずれも Laravel のバージョンを上げて壊れたものではなく、元から壊れていたもの。振る舞いが変わっているので、旧挙動を前提にした手順書があれば更新すること。

| 症状 | 直した内容 |
| --- | --- |
| 発注書の編集が保存できず 500 | `edit()` がフォームに存在しない `is_issued` 等を参照していた。更新は集約の同一性を保ったまま行い、フォームが持たない項目（発行・受注ステータス、ごみ箱フラグ、社内メモ）は現在値を維持する。**id も変わらなくなった**（旧実装は物理削除 → 再作成だった） |
| 編集画面に既存の値が出ない | `orders/form.blade.php` は `$header` / `$details` を受け取りながら一切使っていなかった。`x-order-row` が明細を受け取るようにして描画する |
| `OrderController::view()` が無い | ルートだけあってメソッドが無く 500。ビューも CakePHP のまま（`$this->Html->url()` で 1 行目から落ちる）だったので、明細と金額を出す画面に書き直した |
| 顧客の編集が保存されない | `edit()` に POST 分岐が無く、保存ボタンを押しても何も起きなかった（画面は成功したように見える） |
| 案件のステータス表示が壊れている | `switch ($project->status) { case $project->status === 0: ... }` という `switch (true)` の誤用。さらに一覧が 3 種類・フォームが 8 種類と定義が食い違っていた。`ProjectStatus` enum（8 種類）に一本化 |
| 明細のない発注書で CSV が落ちる | `$details[0]` を無条件参照していた。ヘッダー行を固定で持つようにして、明細が無くても出力できる |
| CSV がカレントディレクトリにファイルを書く | `'./' . $order_no . '.csv'` を作って `readfile()` → `unlink()` していた（`order_no` は未検証）。メモリ上で組み立てて `Response` で返す |
| ユーザーの新規登録画面が 500 | 未保存ユーザーに対して `route('users.2fa', ['id' => null])` を組もうとしていた。2FA ボタンは編集時だけ出す |
| `/downloader/{file}` が必ず 500 | `AddResponseHeaders` が `$response->header()` を呼ぶが、`BinaryFileResponse` にそのメソッドは無い。`$response->headers->set()` に変更 |
| ファイルのアップロード / ダウンロードにパストラバーサル | `$_POST` / `$_FILES` を直接読み、ファイル名を検証せず storage のパスに連結していた。`basename()` で潰し、実パスが保存先の内側にあることを確認する |
| NFC 登録経路でログインできない | 登録時だけ `Hash::make()` していたのに、ログインは平文で完全一致を見ていた。照合方式に合わせて平文で保存する（ハッシュ化は `NfcCredential` の TODO） |
| 2FA が未実装 | `register_2fa_auth()` は空文字を返すスタブだった。TOTP (RFC 6238) を `TwoFactorSecret` に実装（外部ライブラリ不要）。**ログイン時にコードを要求する経路はまだ無い** |
| Academy の問い合わせが保存されない | `fill()` を呼ぶだけで `save()` していなかった。`index()` も中身が空だったので一覧を実装 |
| 旅費精算に費目の入力欄が無い | PDF は交通費・宿泊費などを印字するのに、フォームに入力欄が無く常に空欄だった。費目を入力できるようにして、**合計は内訳から計算する** |
| 精算 CSV の取り込み後に出張申請の一覧へ戻る | リダイレクト先が `/trips` だった |
| 案件の `price` が `$fillable` から漏れている | コントローラが個別代入していたため表面化していなかった |
| `is_deleted` が NULL の発注書が一覧から消える | `where('is_deleted', '!=', 1)` は SQL の NULL 比較の都合で NULL 行を落とす。NULL も「削除されていない」として扱う |
| freee API がエラーでも 200 を返す | cURL の戻り値を検証せずそのまま出していた。失敗は 502 で返す |
| ログイン通知メールの失敗でログインできない | `Mail::send()` の例外がそのまま外に出ていた。通知は失敗してもログインは成立させ、ログに残す |
| 存在しないメールアドレスだけ別のメッセージ | 「The user does not exist.」と表示しており、登録済みかどうかを外から判別できた。メッセージを統一 |

**削除したもの**（いずれも到達不能または実体が無かった）:

- `PrintController` — ルート未登録で、`$this->Clients` など CakePHP の残骸を参照しており動かなかった
- `CalendarController` / `EmailController` — 中身が `//` だけで、ルートも無かった
- `App\Models\Task` — `tasks` テーブルのマイグレーションが存在しない
- `App\Lib\Common` — `calcPer()` は PDF レンダラへ、`getTaxes()` は `TaxRate` enum へ移した。`getMonths()` はどこからも呼ばれていなかった
- `projects/view.blade.php` — 未定義の `$task` を参照しており、ルートも無かった
- ごみ箱の「ごみ箱を空にする」ボタン — リンク先が一覧自身で、何もしないダミーだった
- 出張申請一覧の「ごみ箱に入れる」 — リンク先が発注書の削除ルート (`orders.delete`) を指していた

## in-house-timecard-app からの移植

`novalumo/in-house-timecard-app`（Laravel 10 + Bootstrap 4 の勤怠ツール）から、**docs-manager に無かった 2 機能だけ**を移植した。

| timecard の機能 | docs-manager | 扱い |
| --- | --- | --- |
| Client（クライアント） | Customer（顧客） | かぶり。移植しない |
| Project（プロジェクト） | Project（案件） | かぶり。移植しない |
| Company（会社情報） | Setting / CompanyProfile | かぶり。移植しない |
| User | User | かぶり。移植しない |
| **Contract（契約管理）** | — | **移植した** |
| **Report（勤務報告）** | — | **移植した** |

移植は 2 コミットに分けてある。1 つ目が移植元のファイルを無加工でコピーしたもの、2 つ目がこのリポジトリの構成へ寄せたもの。**移植元との差分を読みたいときは 2 つ目のコミットの diff を見ること。**

### スキーマの変更

移植元のテーブルはそのままでは使えなかったので作り直している。**どちらの表も docs-manager では新規テーブルなので、移行用のマイグレーションは無い。**

- **取引先は `customers` を参照する**。移植元の `client_id` は参照先の無い整数だった。カラム名も `customer_id` に揃えている（`order_headers` と同じ）
- **`reports.project_id` を足した**。移植元の登録フォームには案件のセレクトがあったが `name` 属性が空で送信されず、テーブルにも列が無かった
- **`reports.work_time`（float の時間）を `work_minutes`（整数の分）にした**。`Money` が円を整数で持つのと同じ理由
- **`contracts.contract_id` を `contract_no` に改名**。主キーと紛らわしく、実体は「契約番号」だった。移植元のフォームが送っていた `pid` はテーブルに無いカラムで、値は静かに捨てられていた

### ドメインルール

- **勤務時間は始業・終業が揃っていればそこから計算する**（`Domain\Report\Entity\Report`）。フォームの申告値を使うのは時刻が片方でも欠けているときだけ。発注書の金額をサーバー側で計算し直しているのと同じ考え方
- **日跨ぎの勤務は 24 時間を足して扱う**（`TimeOfDay::minutesUntil()`）。22:00 出社 - 02:00 退社で負の勤務時間にならないようにするため
- **契約の状態はカラムとして持たない**。`ContractTerm::statusOn()` が契約期間と基準日から「開始前 / 契約中 / 終了」を導く。境界は両端とも含む
- **`Report::reconstitute()` は勤務時間を計算し直さない**。保存済みの値をそのまま採る（再計算すると、休憩控除のような規則を後から足したときに過去の記録まで遡って書き換わる）

### 移植時に直した不具合

移植元で壊れていた箇所。いずれも `tests/Feature/{ReportTest,ContractTest}.php` にリグレッションテストがある。

| 症状 | 直した内容 |
| --- | --- |
| 契約の編集画面が必ず 500 | ビューがコントローラの渡さない変数（`$name` / `$pid` / `$start_date` / `$description`）を参照していた。さらに form の action が id 抜きの `route('contracts.update')` で `Missing required parameter` になっていた |
| 総勤務日数が常に 0 | `$reports->sum('work_days')` を呼んでいたが `work_days` というカラムは存在しない |
| 総勤務時間がページ内の分しか出ない | ビューの中でページネーション後の行だけを足していた。集計は絞り込み結果の全件で行う |
| 年月の絞り込みが効かない | 一覧のセレクトが GET で `year` / `month` を送るのに、コントローラはルートパラメータで受けていた（そのルートも登録されていなかった） |
| 年の選択肢が 2020〜2024 の直書き | ビューに `for` ループで埋め込まれており、2025 年以降を選べなかった。`range(2020, 当年 + 1)` にした |
| 担当者・取引先が保存されない | セレクトが名前の文字列を `user_id` / `client` という名前で送っていた。`client` はカラム名（`client_id`）と一致せず捨てられ、`user_id` には名前が入っていた。いずれも id で送る |
| 勤務報告を削除すると 500 | ルートは `destroy` を指すのに、コントローラのメソッド名が `delete` だった |
| 勤務報告の詳細が真っ白 | `reports/show.blade.php` が `@section('content')` の中身ごと空だった |
| 契約を削除できない | 編集画面の削除ボタンが `type="button"` のままで、サーバー側の受け口も無かった |
| 一覧の担当者欄が常に空 | ビューが `$report['who']` という存在しないキーを引いていた |
| 契約一覧の「取引先」列に PID が出る | 存在しないカラム `$contract['pid']` で Jira のリンクを組んでいた |
| 契約一覧の「契約期間」に開始日しか出ない | 終了日を表示していなかった |
| 検証なしで保存される | `store()` / `update()` が `$request->all()` をそのまま `fill()` に渡していた。`SaveReportRequest` / `SaveContractRequest` を通す |

### 移植していないもの

- **Bootstrap 4 のビュー**。画面は Inertia + Svelte で書き直した
- **`maatwebsite/excel` による Excel 出力**。移植元でも呼び出し箇所が無く、依存として宣言されているだけだった
- **`app/Models/`**。Eloquent モデルは `app/Infrastructure/Persistence/Eloquent/Models/` に置く規約に合わせた
- **`SimpleAuth` ミドルウェア**。中身が `// TODO: implement` でコメントアウトされており、実質何もしていなかった。認証は既存の `LoginMiddleware` に任せる
- **ページネーション**。一覧は年月で絞り込むので、1 か月分が上限になる。集計と表示の対象がずれない利点のほうが大きい

## 残っている TODO

コード中に `TODO:` / `FIXME:` コメントで置いてある。特に重いもの:

- **MetaMask ログインが安全でない**: ウォレットアドレスは公開情報なので、いまは「知っていれば入れる」認証になっている。nonce への署名と `ecrecover` による検証に置き換える必要がある（`Domain\User\ValueObject\WalletAddress`）
- **NFC の PIN が平文保存**: ログインが平文の完全一致で照合しているため。両方をハッシュ化する場合、シリアル番号は検索キーとして使うので決定的ハッシュが要る（`Domain\User\ValueObject\NfcCredential`）
- **2FA がログインに繋がっていない**: 設定と検証は動くが、ログイン時にコードを要求していない（`Application\Auth\UseCase\LoginWithPasswordUseCase`）
- **ログイン試行のレート制限が無い**: web ルートには throttle が掛かっていない
- **2FA のリカバリコードが無い**: 端末を失うと復旧できない
- **QR コード画像を生成していない**: `otpauth://` URI を手入力してもらう形になっている

## アーキテクチャ上の重要な癖

### 認証は Laravel Auth を使っていない

`Illuminate\Auth` ではなく **素のセッション**で実装されている。

- セッションへの書き込みは `Infrastructure\Auth\SessionAuthStore`（`Application\Auth\Port\AuthSessionInterface` の実装）が担当する。`session(['user_id', 'name', 'email'])` というキーの構成は変えていない
- `App\Http\Middleware\LoginMiddleware`（ルートミドルウェア名 `login`）が `session('name') === null` で `/login` にリダイレクト
- 認証が必要なルートは `Route::middleware('login')->group(...)` で囲む（`auth` ミドルウェアではない）
- ログインユーザー参照は `session('user_id')` / `session('name')`。`Auth::user()` は機能しない
- 代替ログイン: NFC（Web NFC API）、MetaMask（ウォレットアドレス照合）。いずれも `Application\Auth\UseCase\` にユースケースがある
- **ログイン時にセッション ID を再生成する**（セッション固定攻撃対策）。移行前は行っていなかった
- **`config/session.php` の `serialization` が `json`** なので、セッションに入れてよいのはスカラー値だけ。2FA の設定中シークレットも文字列で出し入れしている（`SessionTwoFactorSetupStore`）
- 2FA は設定と検証が動く（`TwoFactorSecret`）が、**ログイン時にコードを要求する経路はまだ無い**

### フォーム表示と保存はメソッドを分ける

URL は移行前と同じ（同じパスに GET と POST）だが、**コントローラのメソッドは分けてある**。FormRequest による検証を効かせるためで、1 メソッドに兼ねさせると GET でフォームを開いただけで `required` のルールが走ってしまう。

```php
Route::get('/create', 'create')->name('xxx.create');  // フォーム表示
Route::post('/create', 'store');                      // 保存
Route::get('/edit/{id}', 'edit')->name('xxx.edit');
Route::post('/edit/{id}', 'update');
```

**ルート名は GET 側にだけ付ける**（移行前と同じ名前を維持している）。新しい CRUD もこの形に合わせる。

### フラッシュメッセージ

`x-flash` が `flash_message` / `flash_status` / `flash_icon` の 3 キーをまとめて読む。**手で 3 つ書かず `Flash` ヘルパを使う**（色とアイコンの取り違えを避けるため）。

```php
use App\Support\Flash;

return redirect()->route('orders.index')->with(Flash::success('発注書を作成しました'));
// Flash::error() / Flash::warning() もある
```

ドメイン例外を投げた場合は `bootstrap/app.php` の `withExceptions()` が拾って `Flash::error()` 付きで元の画面に戻すので、コントローラで catch する必要はない。

### Eloquent のリレーションは定義されていない

モデルは `$fillable` のみで `hasMany` / `belongsTo` を持たない。関連の組み立ては**リポジトリの仕事**で、コントローラやビューには出てこない。

- 発注書は `order_headers` (1) : `order_details` (N)。外部キーは `order_details.slip_id` → `order_headers.id`（`order_header_id` ではない）
- `OrderRepository` は集約（ヘッダー + 明細）を組み立てて返す。**一覧でも明細を読む**（合計金額を明細から導出するため）が、`whereIn('slip_id', ...)` で 1 クエリにまとめてあり N+1 にはならない
- 保存時、明細は洗い替え（全削除 → 再作成）。行の増減と並び替えが同時に起きるため差分更新にしていない。ヘッダーは `id` を保ったまま更新される
- 顧客名のように「別の集約に属する表示用の値」は、コントローラがまとめて引いて ViewModel に渡す（`OrderController::customerNames()`）

### 論理削除は手動

Laravel の `SoftDeletes` は使わず、`order_headers.is_deleted` (integer) を自前で見ている。`/orders/trash` がゴミ箱、`/orders/restore/{id}` が復元。

判定は `OrderRepository` に閉じている。**`is_deleted` が NULL の行も「削除されていない」として扱う**（移行前の `where('is_deleted', '!=', 1)` は SQL の NULL 比較の都合で NULL 行を落としていた）。

### PDF 生成 (TCPDF / FPDI)

実装は `app/Infrastructure/Pdf/` にあり、`Application\Order\Port\OrderPdfRendererInterface` などのポート越しに呼ばれる。2 方式が混在する。

- **ゼロから描画**: `TcpdfOrderPdfRenderer` — `setasign\Fpdi\Tcpdf\Fpdi` に座標指定で直接書き込む。ロゴ・社印は `resources/img/`
- **テンプレート PDF に重ね書き**: `TcpdfTravelPdfRenderer` / `TcpdfTravelExpensePdfRenderer` — `resources/pdf/*.pdf` を `setSourceFile()` + `importPage()` で読み込み、その上にテキストを配置

**`Output()` は `'S'` を付けてバイト列で受け取る**。ブラウザへ直接書き出さないので、レスポンスの組み立て方はコントローラが決められるし、テストから内容を検証できる。

日本語フォントは `kozminproregular`（明朝）/ `kozgopromedium`（ゴシック）。座標は mm 単位のマジックナンバーなので、レイアウト変更時は実際に PDF を出して確認すること。

**差出人欄は settings テーブルから引く**（`CompanyProfile`）。未登録なら `CompanyProfile::default()` が移行前に直書きされていた値を返すので、設定なしでも PDF は出る。

### 外部連携

外部に出ていく操作はすべて `Application/*/Port/` のインターフェース越しに呼ぶ。実装は `app/Infrastructure/` にある。

- **freee API** (`Infrastructure\Freee\CurlFreeeApiClient`): SDK は使わないが、生の cURL から Laravel の HTTP クライアントに変えてある。5 つのリソース取得は URL の差しかないので `FreeeResource` enum で 1 本にまとめた。認証情報は `config('services.freee.*')`
- **Google Sheets** (`Infrastructure\SpreadSheet\GoogleSheetsClient`): `resources/json/credentials.json`（gitignore 済み、`credentials.example.json` が雛形）+ `config('services.google_sheets.spreadsheet_id')`
- **CSV インポート**: `SplFileObject` + `READ_CSV` で読む（`Infrastructure\Csv\SplFileObjectCsvReader`）。フラグの組み合わせは移行前と同じ。**列は 0 始まりではなく `$row[1]` から読む**（先頭列は使われない）という癖もそのまま
- **CSV の取り込みは 1 トランザクション**。1 行でも日付として解釈できない行があれば全体を取り消す（移行前は 1 行ずつ保存していたため、途中で失敗すると半端に入った）

### フロントエンドの構成

**全 28 画面が Inertia + Svelte 5。Blade のビューはもう画面を描かない。**

SvelteKit は使っていない。ルーティングは Laravel が持ち、Inertia がページを差し替える。

`resources/views/` に残っているのは 3 つだけ。

| ファイル | 用途 |
| --- | --- |
| `app.blade.php` | Inertia のルートテンプレート。`@vite` と `@inertia` を書くだけ |
| `errors/*.blade.php` | Laravel の例外ハンドラが返すエラーページ。Inertia を通らない |
| `emails/login.blade.php` | ログイン通知メールの本文 |

### レイアウト

`app.ts` の `resolve()` がページに既定のレイアウト (`Layouts/Default.svelte`) を割り当てる。ページ側で `<script module>` から `layout` を export すればそちらが優先される（ログイン画面が `Layouts/Auth.svelte` を指定している）。

```svelte
<script module lang="ts">
  export { default as layout } from "../../Layouts/Auth.svelte";
</script>
```

**`#app` は CSS で縦の flex にしてある**（`resources/css/app.css`）。`@inertia` が吐く `<div id="app">` は素の block なので、これをしないとレイアウト側の `flex-1` が伸びる先を持たず、ログイン画面の背景がページの途中で途切れる。

### 削除したもの

移行の完了と同時に消えた。

- **Alpine.js** と `resources/views/components/` の Blade コンポーネント一式（`resources/ts/components/ui/` に移植済み）
- **`layouts/default.blade.php` / `layouts/auth.blade.php`**（`Layouts/Default.svelte` / `Layouts/Auth.svelte` に移植済み）
- **`signup.blade.php`**（ルートが登録されておらず到達不能だった）
- **`lib/nfc-auth.ts` / `lib/metamask-auth.ts`**（`window` の `load` で `document.body` に DOM を組み立てていた。`NfcSignIn.svelte` / `MetamaskSignIn.svelte` に置き換え）
- **`window.novalumo`** と `types/globals.d.ts`（Blade の inline スクリプトから呼ぶ入口だった）

### ディレクトリ

```
resources/ts/
├── Pages/           Inertia のページ。ファイル名がそのまま Inertia::render() の名前
│   ├── Orders/      Index / Trash / Form / Show
│   ├── Customers/   Index / Form
│   ├── Projects/    Index / Form / Analysis
│   ├── Users/       Index / Form / TwoFactor
│   ├── Trips/       Index / Form / Show
│   ├── Expenses/    Index / Form / Show
│   ├── Reports/     Index / Form / Show   (勤務報告)
│   ├── Contracts/   Index / Form           (契約管理)
│   ├── Files/       Index
│   ├── Academy/     Index
│   ├── Auth/        Login (Layouts/Auth を指定)
│   ├── Dashboard.svelte
│   └── Settings.svelte
├── Layouts/         Default.svelte (ナビ・トースト) / Auth.svelte (ログイン)
├── components/
│   ├── ui/          再利用する UI (Button/Card/Input/Table/Modal/…)
│   ├── OrderLines.svelte      明細テーブル (旧 lib/order-form.ts)
│   ├── OrderStatusPill.svelte ステータスピル (旧 lib/status.ts)
│   ├── ProjectsModal.svelte   受注前確認モーダル (案件フォームの子要素)
│   ├── CsvImportModal.svelte  CSV 取り込み (出張申請と旅費精算が使う)
│   ├── NfcSignIn.svelte       NFC でのサインイン (旧 lib/nfc-auth.ts)
│   └── MetamaskSignIn.svelte  MetaMask でのサインイン (旧 lib/metamask-auth.ts)
└── lib/             order-types.ts / master-types.ts / travel-types.ts /
                     report-types.ts (サーバーが渡す JSON の型) など
```

### 書くときの約束

- **Svelte 5 は runes で書く**（`$state` / `$derived` / `$effect`）。DOM の更新はマイクロタスクにまとめられるため、**状態を変えた直後に同期で DOM を読むと更新前の値が返る**
- **内部の画面へのリンクは Inertia 遷移にする**。`Button` / `DropdownItem` は `href` を渡すと `use:inertia` が付く。素のリンクにしたいときは `external` を渡す。**PDF / CSV のダウンロードは必ず `external`**（Inertia の遷移は XHR になり、ファイルを受け取れない）
- **画面から参照する URL はサーバー側で組む**。Ziggy のようなルートヘルパは入れていない。一覧の各行のリンクは ViewModel の `urls` に、ナビやユーザーメニューは共有データに入っている
- **ViewModel は `JsonSerializable` を実装する**。Inertia は props を JSON にして渡すので、メソッド (`displayName()`) の結果もプロパティとして出す必要がある。PHP 側の `jsonSerialize()` と `resources/ts/lib/{order,master,travel,report}-types.ts` は対になっているので、片方を変えたらもう片方も直すこと
- **`urls` は id が null なら null にする**。未保存のエンティティに `route(..., ['id' => null])` は組めない（移行前のユーザー新規登録画面が 500 になっていた原因）。画面側は `urls` の有無でボタンを出し分ける
- **一覧に要らない項目まで props に載せない**。`CustomerView::collection()` は顧客画面用に全項目を出すが、発注書フォームの取引先セレクトは `CustomerView::options()`（id と名前だけ）を使う
- **検証エラーは `FormErrors` に渡す**。`useForm` の `errors` をそのまま渡せばよい
- **`useForm` の `reset()` は「送信時点の値」に戻ることがある**。`useForm` は `onSuccess` の中で現在の値を既定値として取り直すため、`onFinish` で `reset('password')` を呼ぶと送信したパスワードが戻ってくる。消したいときは代入する（`form.password = ""`）。ログイン失敗は `/login` を描き直すだけなので Inertia 的には成功扱いで、ページも状態も残る点にも注意
- **ファイルは `useForm` にそのまま入れる**。値に `File` が混ざると Inertia が自動で `multipart/form-data` に切り替えるので、`enctype` を自分で書く必要は無い（`CsvImportModal`）
- **日付や連番の既定値はサーバーで決める**。画面が `new Date()` を持つと、サーバーの時計とずれるうえテストから固定できない（出張申請フォームの `defaults`）
- **フラッシュのトーストは Svelte の `transition:` を使わない**。Inertia はレイアウトを保持したままページを差し替えるため、表示条件が変わる瞬間にトランジションが中断され、opacity 0 の要素が DOM に残ることがあった。出現は CSS アニメーション (`.toast-enter`)、消すときは DOM から取り除く
- **NFC の読み取りは `lib/nfc-scan.ts`**。旧 `novalumo.ts` は `window.novalumo` 経由で input 要素を受け取り value を直接書き換えていたが、読めた値をコールバックで返す形にしてある（DOM を直接触ると Svelte の状態と食い違う）
- **React は削除済み**: 生きていたのは `ProjectsModal` 1 つだけで、react-router の `<App />`（ダッシュボードの二重描画の原因）と `Calc` / `Example`（マウント先が存在しない）は死にコードだった
- **Inertia は一度削除して入れ直している**: 使われていなかったため Laravel 11 化の際に外したが、今回の移行で再導入した

## CI

**`.github/workflows/ci.yml` がテストと型チェックを回す。デプロイのワークフローは無い。**

| ジョブ | 内容 |
| --- | --- |
| `php` | PHP 8.3 + `composer install` → `./vendor/bin/pest` |
| `frontend` | Node 22 + pnpm 11 → `tsc --noEmit` / `svelte-check` / `pnpm build` |

`pull_request` と `main` への push で走る。2 つのジョブは独立なので並列に動く。ローカルの `just check` と同じものを見ている（`just check` は build を含まない点だけ違う）。

- **`php artisan key:generate` が要る**。`.env.example` の `APP_KEY` は空で、暗号化クッキーのミドルウェアが鍵を要求する。DB は `phpunit.xml` が sqlite の `:memory:` を指定するので、`.env.example` の `DB_CONNECTION=mysql` は使われない
- **テスト前にアセットをビルドする必要は無い**。`tests/TestCase.php` が `withoutVite()` を呼ぶため
- **Node は 22 を指定する**。pnpm 11 が 22.13+、Vite 8 が 22.12+ を要求し、`@tailwindcss/oxide` と `rolldown` のネイティブバイナリは engines が合わないと黙ってスキップされて `Cannot find native binding` で落ちる
- **サードパーティの action は SHA で固定する**（`shivammathur/setup-php` / `pnpm/action-setup`）。バージョンはコメントで併記

### デプロイを作る場合

**デプロイ先のサーバーは廃止済み**で、いま復活させる先は無い。作り直す場合、旧 `deploy.yml` が抱えていた問題を引き継がないよう注意する。

- 旧デプロイは SSH 先で `git reset --hard origin/main` → `yarn install && yarn prod` を実行するだけで、**`composer install` を実行しなかった**
- **マイグレーションも自動実行されなかった**。旧 `runner` にあった `prod:migrate` は `ssh` 先で `migrate:fresh`（＝全テーブル削除）を走らせるものだったので、justfile には移していない
- テストとデプロイが 1 ファイルに同居していたため、デプロイ先が死んだときにテストのジョブごと失われた。**分けておくこと**

## コーディング規約

- StyleCI: `laravel` preset（`no_unused_imports` は無効化）
- インデント: PHP/その他 4 スペース、JS/TS/JSX/YAML 2 スペース（`.editorconfig`）
- コミットメッセージは `add:` / `feat:` / `fix:` などの小文字プレフィックス（Git hook による自動付与は無し）
