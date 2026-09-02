# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## プロジェクト概要

Novalumo 社内向けの業務管理ツール（発注書・出張申請・出張旅費精算・案件管理・顧客管理）。Laravel 13 + Blade + Tailwind CSS v4 のサーバーサイドレンダリング構成で、一部に Svelte/TypeScript を後付けしている。UI・コード内コメントは日本語。

Laravel 8 から 13 へ、メジャーバージョンを 1 つずつ上げてきた（1 メジャー = 1 PR）。**現在 13 で、アップグレードは完了している。**

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

Node 22 なのは、pnpm 11 が Node 22.13+ を、Vite 6 が Node 18+ を要求するため（`nodejs_18` / `nodejs_20` は unstable では EOL 扱いで引けない）。

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

`just dev` を動かすと `public/hot` が作られ、Blade の `@vite` がアセットの参照先を Vite の dev サーバーに切り替える。ページのオリジン（`http://localhost`）と Vite のオリジン（`http://127.0.0.1:5173`）は別になるが、`laravel-vite-plugin` が CORS を通すので問題なく読める。**Vite を止めたら `public/hot` が消えることを確認すること**（残っているとビルド成果物ではなく止まった dev サーバーを見にいくため、画面が真っ白になる）。

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

**共通のフィクスチャは `tests/Pest.php` に置く**。Pest ではテストファイル内で定義した関数もグローバルスコープに入るため、複数ファイルで同名の関数を定義すると再宣言エラーになる。`createUser()` / `actingAsUser()` / `createCustomer()` / `createHeader()` / `orderPayload()` がここにある。

`uses(TestCase::class)->in('Feature')` と `uses(RefreshDatabase::class)->in('Feature')` も `tests/Pest.php` で設定している。Unit テストはフレームワークを起動しない素の PHPUnit TestCase で動く。

**PHPUnit 12**（12.5.33）。`phpunit.xml` で `DB_CONNECTION=sqlite` / `DB_DATABASE=:memory:` を指定しているため、テストは MySQL を必要とせず Sail を起動しなくても回る。DB を使うテストは `RefreshDatabase` を付ける。設定は PHPUnit 10 で入った形式（`<coverage>` ではなく `<source>`）のままで 12 でもそのまま通る。

`phpunit.xml` の `xsi:noNamespaceSchemaLocation` は `10.5` を指したままだが、**PHPUnit 12 はこれを警告しない**（スキーマの参照先はエディタ向けで、PHPUnit 自身は使わない）。動かないわけではないので、慌てて直さなくてよい。

**sqlite と MySQL の型差異に注意**: sqlite (PDO) は integer カラムを文字列で返す。`$header->total_price` は `'1100'` であって `1100` ではないため、テストで数値比較する際は `(int)` にキャストする。この差異は `ProjectController::index()` の挙動まで変える（後述）。

### フロントエンドビルド

**Vite 6**（Laravel Mix から移行済み）。パッケージマネージャは **pnpm**（`pnpm-lock.yaml`）。TypeScript は **6.0**。

**バンドル対象に `.js` は 1 つも無い**。`tsconfig.json` の `include` は `resources/ts/**/*` と `vite.config.ts`。**`tsc --noEmit` も `svelte-check` も現在 0 件で通る**ので、型エラーを増やしたまま放置しないこと（`just check` で両方走る）。

```bash
just dev       # 開発サーバ (HMR)
just build     # 本番ビルド
```

**pnpm 10 以降は依存パッケージの postinstall を既定でブロックする**（サプライチェーン対策）。許可は `pnpm-workspace.yaml` の `allowBuilds` に書く。値はリストではなく「パッケージ名 → bool」のマップである点に注意。設定を足すときは `pnpm approve-builds <pkg> '!<pkg>'` を使うと正しい書式で書き込まれる。現在は Vite の中核である `esbuild` のみ許可している。

**TypeScript 6 は `moduleResolution: "node"` (node10) を非推奨エラーにする**。TS 7 で機能停止するため、`tsconfig.json` は `module: "esnext"` + `moduleResolution: "bundler"` に移行済み。`import.meta.env` の型は `types` に `vite/client` を足して解決している（無いと `ImportMeta` に `env` が生えず `nfc-auth.ts` / `metamask-auth.ts` が型エラーになる）。なお **tsc は emit に使っていない**（`--noEmit` のみ）。実際のトランスパイルは esbuild が行い、esbuild は `module` / `moduleResolution` を読まないので、この変更でビルド成果物は 1 バイトも変わらない。

エントリは `vite.config.ts` の `input` に定義（`resources/css/app.css` と `resources/ts/app.ts`）。出力は `public/build/`（gitignore 済み）で、`manifest.json` を Blade の `@vite` が読む。

**Tailwind v4 はネイティブバイナリ (`@tailwindcss/oxide`) を使い、Node 20+ を要求する**。ホストの Node が古いまま `pnpm install` すると、プラットフォーム別の optional dependency（`@tailwindcss/oxide-darwin-arm64` など）が engines 不一致でスキップされ、ビルド時に `Cannot find native binding` で落ちる。`node_modules` を消して **devShell の中で** 入れ直すこと。

Blade 側は `layouts/default.blade.php` と `layouts/auth.blade.php` が `@vite([...])` を書く。**`@viteReactRefresh` は削除済み**（React を剥がしたため）。

**テストでは `withoutVite()` が必須**。`tests/TestCase.php` の `setUp()` で呼んでいる。これがないと `@vite` がビルド成果物を探しに行き、テスト前に `pnpm build` が必要になる。

Vite は ESM 前提なので `require()` は使えない。バンドル対象の JS/TS は全て ESM で書く。

### Svelte

**Svelte 5**（runes）。**SvelteKit は使っていない** —— ルーティングは Laravel が持ち、Svelte は Blade が描いた DOM に差し込む「島」として使う（「フロントエンドの構成」を参照）。

- ビルドは `@sveltejs/vite-plugin-svelte`。**バージョンを上げるときは Vite との対応に注意**: 7.x の peer は `vite ^8` なので、Vite 6 のこのプロジェクトでは **6.2.4 に固定**している。上げるなら Vite ごと（`laravel-vite-plugin` の最新 3.x も Vite 8 要求）
- `svelte.config.js` は `vitePreprocess()` だけ。`<script lang="ts">` はこれを通して Vite (esbuild) が処理する
- **`tsc` は `.svelte` の中身を見ない**。型を担保するのは `svelte-check` なので、`just check` は両方走らせる。`resources/ts/types/svelte.d.ts` の `declare module "*.svelte"` は「import できること」を tsc に教えるだけのもの（SvelteKit を使っていないと降ってこないため自前で置いている）

### 発注書の明細テーブル (order-form.ts)

金額計算・行の追加/削除・ドラッグでの並べ替えは `resources/ts/lib/order-form.ts` が担当する。**jQuery / jquery-ui は削除済み**で、素の DOM API と HTML5 の Drag and Drop で書かれている。

- 各欄は `name` 属性で引く（`qty[]` / `cost[]` / `tax[]` / `price[]`）。**行ごとの id は持たせていない**。以前は `id="qty_0"` のような添字付き id を振って `for` で回していたが、行を削除しても添字が詰まらず、生きている行を id の存在チェックで拾い直す作りになっていた
- **金額欄 (`price[]`) に入るのは税込金額**。列見出しが「金額」で、小計・消費税・合計は別の行に出しているため
- **`draggable` は掴む直前に立てる**。`pointerdown` の位置が入力欄なら `false`、それ以外なら `true` にする。常時 `true` にすると入力欄の文字選択がドラッグに横取りされる（jquery-ui の `cancel` 既定と同じ考え方）
- `dragover` で `preventDefault()` を呼ばないとドロップ先として認識されない。Firefox は `dataTransfer` に何か入れないとドラッグ自体が始まらない

### スタイル (Tailwind CSS v4)

Bootstrap 5 は削除済み。**Tailwind CSS v4** の CSS-first 構成で、`tailwind.config.js` も PostCSS も autoprefixer も無い。

- Vite プラグイン `@tailwindcss/vite` を `vite.config.js` に足し、`resources/css/app.css` の先頭で `@import "tailwindcss"` する
- 配色などは同ファイルの `@theme` ブロックで CSS 変数として定義する。ブランドカラー `--color-brand-*` は、もともと独自 CSS のアクセントに使われていた `#00acc1` (Material Cyan 600) を基点にしたスケール
- 走査対象は `@source` で `../views` と `../ts` を明示している（v4 は既定でプロジェクト全体を走査するが、明示しておく）
- 独自 CSS は `resources/css/document-table.css`（発注書の明細テーブル）だけ。**SCSS は廃止**（ネストは Lightning CSS が素の CSS として処理する）ので `sass` 依存も外してある。品目候補のオーバーレイ用の `item-overlay.css` もあったが、開閉する JS が存在せず `display: none` のままだったので削除した

**アイコンは `bootstrap-icons`**（Bootstrap 本体とは別プロジェクト）。CDN ではなく npm 依存にして Vite にバンドルさせている。フラッシュメッセージの `flash_icon` にコントローラから `bi-` 名を渡す仕組みは従来どおり。CSS の大半（117KB 中 100KB 程度）はこのアイコン定義で、使うのは十数種だが CDN 時代と同じものなので絞り込んではいない。

### Blade コンポーネントと Alpine.js

Tailwind は CSS しか提供しないので、Bootstrap の JS コンポーネント（modal / dropdown / collapse / toast）は **Alpine.js** に置き換えてある（`resources/ts/lib/alpine.ts` で `Alpine.start()`）。

再利用する UI は `resources/views/components/` の匿名 Blade コンポーネントにまとめてある。`x-button` / `x-input` / `x-select` / `x-textarea` / `x-label` / `x-card` / `x-table` / `x-badge` / `x-toggle` / `x-empty-state` / `x-page-header` / `x-dropdown`（+ `x-dropdown-item` / `x-dropdown-divider`）/ `x-modal` / `x-flash` / `x-order-status` / `x-order-row`。それ以外はユーティリティを直書きする。

落とし穴が 3 つある。

1. **Blade コンポーネントタグの中で Blade ディレクティブを使わない**。`<x-toggle @checked($v) />` のように書くと、コンポーネントタグのパーサが属性として解釈できずタグ自体がコンパイルされず、`<x-toggle>` が未知の HTML 要素としてそのまま出力される（後続の要素がその中に入れ子になり、画面から消える）。`:checked="(bool) $v"` のように **プロパティとして渡す**こと。素の HTML タグの中（`<option @selected(...)>` など）なら問題ない
2. **フラッシュのトーストはヘッダーと重なる**。`x-flash` は `top` プロパティで位置を受け取り、`layouts/default` では既定の `top-20`（h-16 のヘッダーの下）、`layouts/auth` では `top-4` を渡している
3. **発注書の明細行のマークアップは `x-order-row` が唯一の定義**。`orders/form.blade.php` は 5 行を描画したうえで、同じコンポーネントを `<template id="order-row-template">` にも入れておき、`order-form.ts` の行追加処理が `template.content` を複製して足す。コンポーネントは引数を取らない（行ごとの id が無くなったため、添字を渡す必要もなくなった）。以前は同じ HTML が jquery.ts の文字列テンプレートにも書かれていて、しかもその中に CakePHP 時代の `<?php foreach ... ?>` が生のまま残っていた

Svelte 側との連携は DOM の CustomEvent で行う。案件フォームの「保存する」ボタン（Blade）が `open-projects-modal` を投げ、`ProjectsModal.svelte` がそれを拾って開く。Bootstrap の `data-bs-toggle` を使っていた箇所の置き換え。

### ローカルでの動作確認 (sqlite)

MySQL を立てずに画面を確認したいときは sqlite に向けられる。ただし **`php artisan serve` は任意の環境変数を子プロセスに渡さない**（`ServeCommand` の passthrough 許可リストにあるものだけ）。`DB_CONNECTION=sqlite artisan serve` は効かず `.env` の MySQL を見にいくので、PHP 内蔵サーバーを直接叩くこと。

```bash
php -S 127.0.0.1:8123 -t public <ルーターPHP>   # DB_CONNECTION / DB_DATABASE を env で渡す
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
- **サービスプロバイダの登録は `bootstrap/providers.php`**。`config/app.php` に `providers` 配列は無い。現在は `AppServiceProvider` だけで、api の RateLimiter 定義もここに置いている
- **フレームワーク標準のミドルウェアはファイルとして持たない**。`TrustProxies` / `TrimStrings` / `EncryptCookies` などはすべて標準値のままだったので削除した。除外設定を足したくなったら `bootstrap/app.php` の `withMiddleware()` で行う（例: `$middleware->validateCsrfTokens(except: [...])`）
- **残しているカスタムミドルウェアは 2 つだけ**: `LoginMiddleware`（独自セッション認証）と `AddResponseHeaders`（`Server` ヘッダ）
- **翻訳ファイルは `lang/`**（`resources/lang/` ではない。Laravel 9 以降の配置）
- **config は必要なものだけ**。`cors` / `hashing` / `view` / `broadcasting` は全て標準値だったので削除し、フレームワークの既定に任せている

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

`VITE_APP_ENV` は `nfc-auth.ts` / `metamask-auth.ts` がベースパスの判定に使っている。**本番ビルド時にこの変数が設定されていないと、`/docs-manager` プレフィックスの判定が意図せず本番側に倒れる**ので注意。

## Laravel 13 で入れた設定

- **CSRF ミドルウェアは `PreventRequestForgery`**: Laravel 13 で `VerifyCsrfToken` からリネームされ、`Sec-Fetch-Site` ヘッダによるリクエスト元検証が加わった。**継承した独自クラスは持っておらず**、web グループにフレームワーク標準の `Illuminate\Foundation\Http\Middleware\PreventRequestForgery` がそのまま入っている（明示的に名前を書いているのは `config/sanctum.php` の `validate_csrf_token` だけ）。`VerifyCsrfToken` / `ValidateCsrfToken` は非推奨エイリアスとして残っているが使わないこと
- **`handle()` は `hasValidOrigin()` を `tokensMatch()` より先に評価する**。`Sec-Fetch-Site: same-origin` が付いていればトークンを見ずに通す。そのためブラウザからの同一オリジン fetch は CSRF トークンが無くても 200 になり、**ブラウザ操作だけではトークン検証の経路を確認できない**。検証したいときは `Sec-Fetch-Site` を送らない curl を使うこと（トークン無し・不正なら 419 になる）
- **`config/session.php` の `serialization` は `json`**: PHP の unserialize による gadget chain 攻撃を避けるため。このアプリはセッションに `user_id` / `name` / `email` の文字列しか入れていないので json で足りる
- **`config/cache.php` の `serializable_classes` は `false`**: キャッシュから PHP オブジェクトを復元しない設定。キャッシュにオブジェクトを入れていないため false のままでよい
- **`composer.json` の `allow-plugins` に `pestphp/pest-plugin`**: composer 2.2 以降はプラグインの実行に明示的な許可が要る。増やすときは必要最小限にする

## 既知の不具合（アップグレード前から壊れている）

リグレッションテスト追加時に判明したもの。**Laravel のバージョンを上げて壊れたのではなく、元から壊れている**。該当テストは `markTestIncomplete()` で理由付きで残してあるので、直したら外すこと。

- **発注書の編集が保存できない**: `OrderController::edit()` は `$req['is_issued']` / `is_ordered` / `is_deleted` / `is_converted` を参照するが、`resources/views/orders/form.blade.php` に対応する入力が一切ない。POST すると `Undefined index` で 500 になる
- **`OrderController::view()` が存在しない**: ルート `orders.view` (`/orders/view/{id}`) は登録されているのにメソッドが無く 500。一覧にリンクが無いため UI からは到達しない
- **顧客の編集が保存されない**: `CustomerController::edit()` に `isMethod('post')` の分岐が無く、POST しても常に view を返すだけ
- **案件のステータス表示が壊れている**: `ProjectController::index()` が `switch ($project->status) { case $project->status === 0: ... }` と書かれている（`switch (true)` の誤用）。sqlite では status 1・2 が「未知」になる。値の型が変わる MySQL では結果が変わりうる
- **明細のない発注書は CSV 出力できない**: `OrderController::csv()` が `$details[0]` を無条件に参照する
- **`OrderController::csv()` はカレントディレクトリにファイルを書く**: `'./' . $order_no . '.csv'` を作って `readfile()` 後に `unlink()` する。`order_no` は検証されていない

Tailwind 移行時にブラウザで触って追加で判明したもの。

- **ユーザーの新規登録画面が 500**: `users/form.blade.php` の 2FA リンクが `route('users.2fa', ['id' => $user->id])` を呼ぶが、`UserController::create()` は未保存の `new User()` を渡すため `id` が null で `Missing required parameter` になる。編集画面 (`/users/edit/{id}`) は動く
- **`orders/view.blade.php` は CakePHP のまま**: `$this->Html->url()` / `WWW_ROOT` / `APP` を使っており Laravel では 1 行目で落ちる。`OrderController::view()` が無いのでそもそも到達しない。Bootstrap のクラスと jQuery 前提の inline スクリプトが残っているが、到達しないため Tailwind 移行の対象外にしてある
- **`nfc-auth.ts` が Bootstrap のクラスを付けている**: `createElement` で作る要素に `form-control` / `btn btn-primary` を付けるが、Bootstrap の CSS はもう無いのでスタイルの当たらない裸の要素になる（機能自体は動く）

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

### フロントエンドの構成

主体は **Blade + Tailwind + Alpine**。**Svelte 5** は「Blade が描いた DOM の特定の場所に差し込む島」として同居している。SvelteKit は使っていない（SPA ではなく、ルーティングは Laravel が持つ）。

- **マウントは `resources/ts/app.ts` の `ISLANDS` に集約する**。マウント先が無い画面では何もしない。以前は React コンポーネントが各ファイル末尾で自分をマウントしていたが、どこに何が生えるのか追えなかったため一箇所にまとめた
- 現在の島は `ProjectsModal.svelte`（`#projects-modal`、案件フォームの受注前確認）**1 つだけ**
- Svelte コンポーネントは `resources/ts/components/` に置く。`resources/ts/` の外に出すと Tailwind の `@source` を足す必要が出る
- **Svelte 5 は runes で書く**（`$state` / `$derived` / `$effect`）。DOM の更新はマイクロタスクにまとめられるため、**テストやコンソールから状態を変えた直後に同期で DOM を読むと更新前の値が返る**（`await tick()` 相当の待機を挟むこと）
- `resources/ts/lib/novalumo.ts` は `window.novalumo` として公開され、Blade の inline スクリプトから呼ばれる
- **React は削除済み**: 生きていたのは `ProjectsModal` 1 つだけで、react-router の `<App />`（ダッシュボードの二重描画の原因）と `Calc` / `Example`（マウント先が存在しない）は死にコードだった
- **Inertia は削除済み**: 一度も使われていなかったため、Laravel 11 化の際に composer の `inertiajs/inertia-laravel` と `HandleInertiaRequests` ミドルウェアごと削除した（npm 側の `@inertiajs/*` は Vite 移行時に削除済み）

## デプロイ / CI

**現在 CI・デプロイのワークフローは無い**。`.github/workflows/deploy.yml` は、デプロイ先サーバーが廃止され `main` への push のたびに SSH 接続で失敗する状態になっていたため一旦削除した。同ファイルに同居していた `php-tests`（PHPUnit）ジョブも同時に失われている。

作り直す場合、旧ワークフローが抱えていた問題を引き継がないよう注意する。

- 旧デプロイは SSH 先で `git reset --hard origin/main` → `yarn install && yarn prod` を実行するだけで、**`composer install` を実行しなかった**
- **マイグレーションも自動実行されなかった**。旧 `runner` にあった `prod:migrate` は `ssh` 先で `migrate:fresh`（＝全テーブル削除）を走らせるものだったので、justfile には移していない
- Node.js のテストジョブはコメントアウトされていた（jest のテストファイル自体が未作成）

## コーディング規約

- StyleCI: `laravel` preset（`no_unused_imports` は無効化）
- インデント: PHP/その他 4 スペース、JS/TS/JSX/YAML 2 スペース（`.editorconfig`）
- コミットメッセージは `add:` / `feat:` / `fix:` などの小文字プレフィックス（Git hook による自動付与は無し）
