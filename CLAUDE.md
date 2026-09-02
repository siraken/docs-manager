# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## プロジェクト概要

Novalumo 社内向けの業務管理ツール（発注書・出張申請・出張旅費精算・案件管理・顧客管理）。Laravel 13 + Blade + Tailwind CSS v4 のサーバーサイドレンダリング構成で、一部に Svelte/TypeScript を後付けしている。UI・コード内コメントは日本語。

Laravel 8 から 13 へ、メジャーバージョンを 1 つずつ上げてきた（1 メジャー = 1 PR）。**現在 13 で、アップグレードは完了している。**

サーバー側は **Domain / Application / Infrastructure / Presentation の 4 層**に分けてある（「レイヤー構成」を参照）。以前はコントローラに DB アクセス・金額計算・PDF 描画・外部 API 呼び出しが直書きされていた。

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

**共通のフィクスチャは `tests/Pest.php` に置く**。Pest ではテストファイル内で定義した関数もグローバルスコープに入るため、複数ファイルで同名の関数を定義すると再宣言エラーになる。`createUser()` / `actingAsUser()` / `createCustomer()` / `createHeader()` / `orderPayload()` / `walletAddress()` がここにある。**この制約は Unit テストにも及ぶ**ので、テストファイル内でヘルパを定義するときは他ファイルと衝突しない名前にすること（例: `tests/Unit/OrderTest.php` の `newOrder()` / `orderLine()`）。

フィクスチャは Eloquent モデルを直接使う。ユースケース経由で用意すると「準備」と「検証対象」が同じ経路になり、リグレッションを検出できなくなる。

`uses(TestCase::class)->in('Feature')` と `uses(RefreshDatabase::class)->in('Feature')` も `tests/Pest.php` で設定している。

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

エントリは `vite.config.ts` の `input` に定義（`resources/css/app.css` と `resources/ts/app.ts`）。出力は `public/build/`（gitignore 済み）で、`manifest.json` を Blade の `@vite` が読む。

**ネイティブバイナリを使う依存が 2 つある**: Tailwind v4 の `@tailwindcss/oxide` と、Vite 8 のバンドラである `rolldown`。どちらもプラットフォーム別のプリビルドを optional dependency として配り（`@tailwindcss/oxide-darwin-arm64` / `@rolldown/binding-darwin-arm64`）、`engines` に Node のバージョン制約を持つ。**ホストの Node が古いまま `pnpm install` すると engines 不一致で黙ってスキップされ**、ビルド時に `Cannot find native binding` で落ちる。`node_modules` を消して **devShell の中で** 入れ直すこと。

Blade 側は `layouts/default.blade.php` と `layouts/auth.blade.php` が `@vite([...])` を書く。**`@viteReactRefresh` は削除済み**（React を剥がしたため）。

**テストでは `withoutVite()` が必須**。`tests/TestCase.php` の `setUp()` で呼んでいる。これがないと `@vite` がビルド成果物を探しに行き、テスト前に `pnpm build` が必要になる。

Vite は ESM 前提なので `require()` は使えない。バンドル対象の JS/TS は全て ESM で書く。

### Svelte

**Svelte 5**（runes）。**SvelteKit は使っていない** —— ルーティングは Laravel が持ち、Svelte は Blade が描いた DOM に差し込む「島」として使う（「フロントエンドの構成」を参照）。

- ビルドは `@sveltejs/vite-plugin-svelte` 7.x。**バージョンを上げるときは Vite との対応に注意**: peer が `vite ^8` で、`laravel-vite-plugin` 3.x も同じく Vite 8 を要求する。この 3 つは足並みを揃えて上げること
- `svelte.config.js` は `vitePreprocess()` だけ。`<script lang="ts">` はこれを通して Vite（8 では Oxc）が処理する
- **`tsc` は `.svelte` の中身を見ない**。型を担保するのは `svelte-check` なので、`just check` は両方走らせる。`resources/ts/types/svelte.d.ts` の `declare module "*.svelte"` は「import できること」を tsc に教えるだけのもの（SvelteKit を使っていないと降ってこないため自前で置いている）

### 発注書の明細テーブル (order-form.ts)

金額計算・行の追加/削除・ドラッグでの並べ替えは `resources/ts/lib/order-form.ts` が担当する。**jQuery / jquery-ui は削除済み**で、素の DOM API と HTML5 の Drag and Drop で書かれている。

- 各欄は `name` 属性で引く（`qty[]` / `cost[]` / `tax[]` / `price[]`）。**行ごとの id は持たせていない**。以前は `id="qty_0"` のような添字付き id を振って `for` で回していたが、行を削除しても添字が詰まらず、生きている行を id の存在チェックで拾い直す作りになっていた
- **金額欄 (`price[]`) に入るのは税込金額**。列見出しが「金額」で、小計・消費税・合計は別の行に出しているため
- **フロントの計算結果はサーバーに送っても使われない**。保存される金額は `Domain\Order\Entity\OrderLine` が数量・単価・税区分から計算し直す（`SaveOrderRequest` は `price[]` / `subtotal` / `taxTotal` / `totalPrice` を読まない）。ここの計算式を変えるときは `OrderLine` 側も合わせること。税率の定義は `Domain\Order\ValueObject\TaxRate` が正
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
3. **発注書の明細行のマークアップは `x-order-row` が唯一の定義**。`orders/form.blade.php` は既存の明細 + 空行を描画したうえで、同じコンポーネントを `<template id="order-row-template">` にも入れておき、`order-form.ts` の行追加処理が `template.content` を複製して足す。コンポーネントは `:line`（既存の明細。新規行なら null）と `:tax-options` を受け取る。以前は同じ HTML が jquery.ts の文字列テンプレートにも書かれていて、しかもその中に CakePHP 時代の `<?php foreach ... ?>` が生のまま残っていた

Svelte 側との連携は DOM の CustomEvent で行う。案件フォームの「保存する」ボタン（Blade）が `open-projects-modal` を投げ、`ProjectsModal.svelte` がそれを拾って開く。Bootstrap の `data-bs-toggle` を使っていた箇所の置き換え。

### ビューに渡すのは ViewModel

**Blade に Eloquent モデルもドメインエンティティも渡さない**。`app/Presentation/Http/ViewModels/` の readonly クラス（`OrderView` / `CustomerView` / `ProjectView` / `UserView` / `TravelView` / `TravelExpenseView` / `CompanyProfileView` / `AcademyInquiryView`）に整形済みの値を詰めて渡す。

- プロパティはキャメルケース（`$row->issuedDateLabel`）。移行前はカラム名で `$row['issued_date']` と引いていたため、DB のカラム名を変えると画面が壊れた
- 日付や金額は**整形済みの値も持たせる**（`issuedDate` = `2026-09-01` / `issuedDateLabel` = `2026/09/01`、`total` = `1650` / `totalLabel` = `1,650`）。前者はフォームの `value`、後者は表示に使う
- 一覧には `::collection()` で `Illuminate\Support\Collection` を返す。テストが `viewData('orders')->keyBy(...)` のように扱えるようにするため
- 新規作成フォームには `::empty()` を渡す（null チェックを Blade に持ち込まないため）

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
- **サービスプロバイダの登録は `bootstrap/providers.php`**。`config/app.php` に `providers` 配列は無い。`AppServiceProvider`（api の RateLimiter 定義）と `DomainServiceProvider`（インターフェースと実装の対応）の 2 つ
- **フレームワーク標準のミドルウェアはファイルとして持たない**。`TrustProxies` / `TrimStrings` / `EncryptCookies` などはすべて標準値のままだったので削除した。除外設定を足したくなったら `bootstrap/app.php` の `withMiddleware()` で行う（例: `$middleware->validateCsrfTokens(except: [...])`）
- **残しているカスタムミドルウェアは 2 つだけ**: `LoginMiddleware`（独自セッション認証）と `AddResponseHeaders`（`Server` ヘッダ）。どちらも `app/Presentation/Http/Middleware/` にある
- **`AddResponseHeaders` は `$response->headers->set()` を使う**。`$response->header()` は `Illuminate\Http\Response` のメソッドで、ファイルダウンロードで返る `BinaryFileResponse` には無い。以前はこれが原因で `/downloader/{file}` が必ず 500 になっていた
- **例外の HTTP への変換は `bootstrap/app.php` の `withExceptions()`**。`EntityNotFoundException` を 404 に、それ以外の `DomainException` を「元の画面へリダイレクト + フラッシュ」（JSON リクエストなら 422）に落としている。ドメイン層がフレームワークを知らずに済むのはここで受けているため
- **翻訳ファイルは `lang/`**（`resources/lang/` ではない。Laravel 9 以降の配置）
- **config は必要なものだけ**。`cors` / `hashing` / `view` / `broadcasting` は全て標準値だったので削除し、フレームワークの既定に任せている
- **外部サービスの設定は `config/services.php`**。freee と Google スプレッドシートの認証情報をここに集約している。**`env()` を直接読まないこと**（`config:cache` した環境では `.env` を読み直さないため null になる）

## レイヤー構成

サーバー側は 4 層に分かれている。**依存は外側から内側に向かう一方向**で、ドメイン層は Laravel も Eloquent も知らない。

```
app/
├── Domain/           業務ルール。フレームワーク非依存
│   ├── Shared/           Money、ドメイン例外
│   ├── Order/            Entity/Order・OrderLine、ValueObject、Repository インターフェース
│   ├── Customer/ Project/ User/ Travel/ Academy/ Setting/
├── Application/      ユースケース。「何をするか」の手順
│   ├── <文脈>/UseCase/   1 クラス 1 ユースケース (execute() だけを持つ)
│   ├── <文脈>/Input/     ユースケースへの入力 DTO
│   ├── <文脈>/Port/      外部に出ていく操作の抽象 (PDF 描画・CSV 読み書き・メール・セッション・外部 API)
│   └── Shared/           DateParser、RenderedDocument
├── Infrastructure/   技術的な詳細。Port と Repository の実装
│   ├── Persistence/Eloquent/  Models・Mapper・各 Repository
│   ├── Pdf/ Csv/ Auth/ Mail/ Freee/ SpreadSheet/
└── Presentation/Http/  HTTP との変換だけ
    ├── Controllers/   ユースケースを呼んで View か Response を返す
    ├── Requests/      FormRequest。検証と Input DTO への組み替え
    ├── ViewModels/    Blade に渡す整形済みの値
    ├── Middleware/
    └── Support/Flash  フラッシュメッセージの 3 キーを組み立てる
```

**新しい機能を足すときの流れ**:

1. 業務ルールがあるなら `Domain` にエンティティ / 値オブジェクトを置く（テストは `tests/Unit/`）
2. 手順を `Application/<文脈>/UseCase/` に 1 クラスで書く。必要な入力は `Input/` の DTO にする
3. DB や外部サービスに触るなら、まず `Domain/.../Repository/` か `Application/.../Port/` にインターフェースを置き、実装を `Infrastructure` に書く
4. `DomainServiceProvider::$bindings` に「インターフェース => 実装」を 1 行足す
5. `Presentation` に FormRequest とコントローラのメソッドを足し、`routes/web.php` に登録する

**守ること**:

- **ドメイン層に `use Illuminate\...` を書かない**。書きたくなったらそれは Infrastructure の関心
- **コントローラに業務ロジックを書かない**。分岐が出てきたらユースケース側へ
- **Eloquent モデルはリポジトリの外に出さない**。ビューへはドメインのエンティティではなく ViewModel を渡す（Blade が `$row['is_issued']` のようにカラム名で引くと、DB の都合が画面に漏れる）
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

`VITE_APP_ENV` は `nfc-auth.ts` / `metamask-auth.ts` がベースパスの判定に使っている。**本番ビルド時にこの変数が設定されていないと、`/docs-manager` プレフィックスの判定が意図せず本番側に倒れる**ので注意。

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
| `OrderController::view()` が無い | ルートだけあってメソッドが無く 500。ビューも CakePHP のまま（`$this->Html->url()` で 1 行目から落ちる）だったので、明細と金額を出す Blade に書き直した |
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
- `Presentation\Http\Middleware\LoginMiddleware`（ルートミドルウェア名 `login`）が `session('name') === null` で `/login` にリダイレクト
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

**ルート名は GET 側にだけ付ける**（移行前と同じ名前を維持しているので、Blade の `route()` は変更不要）。新しい CRUD もこの形に合わせる。

### フラッシュメッセージ

`x-flash` が `flash_message` / `flash_status` / `flash_icon` の 3 キーをまとめて読む。**手で 3 つ書かず `Flash` ヘルパを使う**（色とアイコンの取り違えを避けるため）。

```php
use App\Presentation\Http\Support\Flash;

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

**`Output()` は `'S'` を付けてバイト列で受け取る**。ブラウザへ直接書き出さないので、レスポンスの組み立て方は Presentation 層が決められるし、テストから内容を検証できる。

日本語フォントは `kozminproregular`（明朝）/ `kozgopromedium`（ゴシック）。座標は mm 単位のマジックナンバーなので、レイアウト変更時は実際に PDF を出して確認すること。

**差出人欄は settings テーブルから引く**（`CompanyProfile`）。未登録なら `CompanyProfile::default()` が移行前に直書きされていた値を返すので、設定なしでも PDF は出る。

### 外部連携

外部に出ていく操作はすべて `Application/*/Port/` のインターフェース越しに呼ぶ。実装は `app/Infrastructure/` にある。

- **freee API** (`Infrastructure\Freee\CurlFreeeApiClient`): SDK は使わないが、生の cURL から Laravel の HTTP クライアントに変えてある。5 つのリソース取得は URL の差しかないので `FreeeResource` enum で 1 本にまとめた。認証情報は `config('services.freee.*')`
- **Google Sheets** (`Infrastructure\SpreadSheet\GoogleSheetsClient`): `resources/json/credentials.json`（gitignore 済み、`credentials.example.json` が雛形）+ `config('services.google_sheets.spreadsheet_id')`
- **CSV インポート**: `SplFileObject` + `READ_CSV` で読む（`Infrastructure\Csv\SplFileObjectCsvReader`）。フラグの組み合わせは移行前と同じ。**列は 0 始まりではなく `$row[1]` から読む**（先頭列は使われない）という癖もそのまま
- **CSV の取り込みは 1 トランザクション**。1 行でも日付として解釈できない行があれば全体を取り消す（移行前は 1 行ずつ保存していたため、途中で失敗すると半端に入った）

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
