# フロントエンド

全画面が Inertia + Svelte 5。SvelteKit は使わず、ルーティングは Laravel が持つ。

## 構成

**全 42 画面が Inertia + Svelte 5。Blade のビューはもう画面を描かない。**

SvelteKit は使っていない。ルーティングは Laravel が持ち、Inertia がページを差し替える。

`resources/views/` に残っているのは 3 つだけ。

| ファイル | 用途 |
| --- | --- |
| `app.blade.php` | Inertia のルートテンプレート。`@vite` と `@inertia` を書くだけ |
| `errors/*.blade.php` | Laravel の例外ハンドラが返すエラーページ。Inertia を通らない |
| `emails/login.blade.php` | ログイン通知メールの本文 |

## ディレクトリ

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
│   ├── Journal/     Index / Form / TrialBalance (仕訳帳・残高試算表)
│   ├── Accounts/    Index / Form           (勘定科目マスタ)
│   ├── Enrollments/ Index / Form           (受講記録)
│   ├── Courses/     Index / Form           (講座マスタ)
│   ├── Submissions/ Index / Form           (提出物)
│   ├── Assignments/ Index / Form           (課題マスタ)
│   ├── Chat/        Index                  (チャット)
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
                     report-types.ts / accounting-types.ts /
                     learning-types.ts (サーバーが渡す JSON の型) など
```

## ビルド (Vite 8)

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

## Svelte

**Svelte 5**（runes）。**SvelteKit は使っていない** —— ルーティングは Laravel が持ち、Inertia がページを差し替える（上の「構成」を参照）。ページ全体が Svelte で、Blade の DOM に差し込む「島」は無い。

- ビルドは `@sveltejs/vite-plugin-svelte` 7.x。**バージョンを上げるときは Vite との対応に注意**: peer が `vite ^8` で、`laravel-vite-plugin` 3.x も同じく Vite 8 を要求する。この 3 つは足並みを揃えて上げること
- `svelte.config.js` は `vitePreprocess()` だけ。`<script lang="ts">` はこれを通して Vite（8 では Oxc）が処理する
- **`tsc` は `.svelte` の中身を見ない**。型を担保するのは `svelte-check` なので、`just check` は両方走らせる。`resources/ts/types/svelte.d.ts` の `declare module "*.svelte"` は「import できること」を tsc に教えるだけのもの（SvelteKit を使っていないと降ってこないため自前で置いている）

## スタイル (Tailwind CSS v4)

Bootstrap 5 は削除済み。**Tailwind CSS v4** の CSS-first 構成で、`tailwind.config.js` も PostCSS も autoprefixer も無い。

- Vite プラグイン `@tailwindcss/vite` を `vite.config.js` に足し、`resources/css/app.css` の先頭で `@import "tailwindcss"` する
- 配色などは同ファイルの `@theme` ブロックで CSS 変数として定義する。ブランドカラー `--color-brand-*` は、もともと独自 CSS のアクセントに使われていた `#00acc1` (Material Cyan 600) を基点にしたスケール
- 走査対象は `@source` で `../views` と `../ts` を明示している（v4 は既定でプロジェクト全体を走査するが、明示しておく）
- 独自 CSS は `resources/css/document-table.css`（発注書の明細テーブル）だけ。**SCSS は廃止**（ネストは Lightning CSS が素の CSS として処理する）ので `sass` 依存も外してある。品目候補のオーバーレイ用の `item-overlay.css` もあったが、開閉する JS が存在せず `display: none` のままだったので削除した

**アイコンは `bootstrap-icons`**（Bootstrap 本体とは別プロジェクト）。CDN ではなく npm 依存にして Vite にバンドルさせている。フラッシュメッセージの `flash_icon` にコントローラから `bi-` 名を渡す仕組みは従来どおり。CSS の大半（117KB 中 100KB 程度）はこのアイコン定義で、使うのは十数種だが CDN 時代と同じものなので絞り込んではいない。

## UI コンポーネント

再利用する UI は `resources/ts/components/ui/` にある。`Button` / `Input` / `Select` / `Textarea` / `Toggle` / `Label` / `Card` / `Table` / `Badge` / `EmptyState` / `PageHeader` / `Modal` / `Dropdown`（+ `DropdownItem` / `DropdownDivider`）/ `Flash` / `FormErrors` / `DetailList`。それ以外はユーティリティを直書きする。

**Bootstrap 5 の JS コンポーネント（modal / dropdown / collapse / toast）は、一度 Alpine.js に置き換えたあと Svelte に移した**。Alpine とその Blade 版コンポーネント (`resources/views/components/`) は Inertia 化の完了と同時に削除してある。

**フラッシュのトーストはヘッダーと重なる**。`Flash` は `top` プロパティで位置を受け取り、`Layouts/Default` では既定の `top-20`（h-16 のヘッダーの下）、`Layouts/Auth` では `top-4` を渡している。

## レイアウト

`app.ts` の `resolve()` がページに既定のレイアウト (`Layouts/Default.svelte`) を割り当てる。ページ側で `<script module>` から `layout` を export すればそちらが優先される（ログイン画面が `Layouts/Auth.svelte` を指定している）。

```svelte
<script module lang="ts">
  export { default as layout } from "../../Layouts/Auth.svelte";
</script>
```

**`#app` は CSS で縦の flex にしてある**（`resources/css/app.css`）。`@inertia` が吐く `<div id="app">` は素の block なので、これをしないとレイアウト側の `flex-1` が伸びる先を持たず、ログイン画面の背景がページの途中で途切れる。

## 画面に渡すのは ViewModel

**Eloquent モデルもドメインエンティティも画面に渡さない**。`app/Http/ViewModels/` の readonly クラス（`OrderView` / `CustomerView` / `ProjectView` / `UserView` / `TravelView` / `TravelExpenseView` / `CompanyProfileView` / `AcademyInquiryView`）に整形済みの値を詰めて渡す。

- プロパティはキャメルケース（`$row->issuedDateLabel`）。移行前はカラム名で `$row['issued_date']` と引いていたため、DB のカラム名を変えると画面が壊れた
- 日付や金額は**整形済みの値も持たせる**（`issuedDate` = `2026-09-01` / `issuedDateLabel` = `2026/09/01`、`total` = `1650` / `totalLabel` = `1,650`）。前者はフォームの `value`、後者は表示に使う
- 一覧には `::collection()` で `Illuminate\Support\Collection` を返す
- 新規作成フォームには `::empty()` を渡すか、props を `null` にして画面側で分岐する。既定値をサーバーが持つなら前者（`TravelExpenseView`）、持たないなら後者（`CustomerView` / `ProjectView` / `UserView`）

## サーバーへの送信

**すべて Inertia 経由で送る。HTTP クライアントは入れていない。**

- フォームの送信は `useForm` の `form.post(url)`
- フォーム以外（NFC・MetaMask のサインイン、削除ボタンなど）は `router.post()` / `router.delete()`
- 一覧の再取得は `router.reload({ only: ['messages'] })` のような部分リロード（チャットが使っている）

Inertia が CSRF トークンとヘッダを面倒見るので、こちら側で用意するものは無い。

**専用の JSON エンドポイントを足さないこと。** データが要るなら Inertia の props で渡し、更新が要るなら部分リロードで取り直す。この方針のおかげで、以前 axios → `ky` と乗り換えながら維持していた `lib/http.ts`（XSRF トークンの付与と `X-Requested-With` を手で再現していた）は不要になり、依存ごと削除した。

## 環境変数

Vite はビルド時に `import.meta.env.VITE_*` を値へ埋め込む。**`process.env.MIX_*` は解決されない**（Mix 時代の書き方が残っていると常に `undefined` になる）。

**いま `import.meta.env.VITE_*` を読むコードは無い**。`VITE_APP_ENV` は `nfc-auth.ts` / `metamask-auth.ts` がベースパス（`/docs-manager` プレフィックス）の判定に使っていたが、両ファイルは Inertia 化で削除された。URL はすべてサーバー側で組んでいるので、フロントがベースパスを知る必要そのものが無くなっている。`.env.example` の `VITE_APP_ENV` は残してあるが、現状どこからも読まれない。

`tsconfig.json` の `types` にある `vite/client` は引き続き要る。`app.ts` の `import.meta.glob` の型がこれで解決されるため。

## 発注書の明細テーブル (OrderLines.svelte)

金額計算・行の追加/削除・ドラッグでの並べ替えは `resources/ts/components/OrderLines.svelte` が担当する。

移行の経緯: jQuery + jquery-ui → 素の DOM API (`lib/order-form.ts`) → Svelte。DOM を走査していた頃は行が状態として存在せず、追加は `<template>` の複製、並べ替えは DOM の付け替えで表現していた。いまは行が配列なので、どれも配列操作になる。

- **行の型は `lib/order-line.ts` の `OrderLineDraft`**。数量も単価も文字列で持つ（input の値がそのまま入るため、"1," のような途中の入力を保持できる必要がある）
- **表示している金額は画面のためだけのもの**。保存される金額は `Domain\Order\Entity\OrderLine` が数量・単価・税区分から計算し直す（フォームは金額を送らない）。`OrderLines.svelte` の `TAX_RATES` は表示用の写しで、正は `Domain\Order\ValueObject\TaxRate`
- **`draggable` は掴む直前に立てる**。`pointerdown` の位置が入力欄なら立てない。常時 `true` にすると入力欄の文字選択がドラッグに横取りされる（jquery-ui の `cancel` 既定と同じ考え方）
- `dragover` で `preventDefault()` を呼ばないとドロップ先として認識されない。Firefox は `dataTransfer` に何か入れないとドラッグ自体が始まらない

## 書くときの約束

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
