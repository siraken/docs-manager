# novalumo/e-learning を参考にした社内研修

`novalumo/e-learning`（Laravel 8 + Bootstrap 5 の作りかけの e ラーニングシステム）を参考に、**講座**と**受講記録**を新規開発したもの。

timecard のときと違い、**そのまま持ってこられる実装はほとんど無かった**。移植ではなく、参考にした新規開発になっている。

## 参考元の完成度

調査した時点の `novalumo/e-learning` の状態。

| 機能 | テーブル | コントローラ | ビュー | 実装状況 |
| --- | --- | --- | --- | --- |
| チャット | `chats`（uid_from / uid_to / body） | 投稿・取得・状態 | あり | ほぼ動く（宛先が `999` 固定、オンライン状態は未実装） |
| ユーザー | `users`（disp_name / username / icon_image / type） | 一覧・登録 | あり | 動く（`type` は 0=一般 / 1=講師 / 999=管理者。保存はするが判定に使われていない） |
| ログイン | — | あり | あり | 動く。素のセッション認証で docs-manager と同型 |
| ホーム | — | あり | Bootstrap のダミーデータ | 見た目だけ |
| 個人設定 | — | `index` のみ | あり | 表示のみ |
| **講座 `classes`** | `title` + `exp` のみ | 無い | 無い | **テーブルだけ** |
| **課題 `tasks`** | `id` + `timestamps` のみ | view を返すだけ | `settings` の丸写し | **空** |
| **記録 `records`** | `id` + `timestamps` のみ | 空スタブ | `settings` の丸写し | **空** |

**`classes` / `tasks` / `records` を参照するコードは 1 行も無かった。** e ラーニングの核である講座・課題・受講記録は事実上まったく実装されていない。`tasks` と `records` のビューは `settings` のコピーで、見出しだけ差し替えたものだった。

## 取り込んだもの

- **講座 (`courses`)** — 参考元の `classes` テーブル（`title` + `exp`）が出発点
- **受講記録 (`enrollments`)** — 参考元の `records` に相当するが、あちらは `id` と `timestamps` しか無く**実質すべて新規設計**

### テーブル名を classes から courses に変えた理由

`class` は PHP の予約語で、`App\Models\Class` のようなモデルを定義できない。参考元がモデルを作っていなかったのも、おそらくこれが理由。

## ドメインルール

- **状態と日付は必ず噛み合う**（`Enrollment::reconcile()`）。未受講なら日付を持たず、受講中なら完了日を持たず、完了なら完了日が要る。「状態だけ戻して日付が取り残される」という不整合を作れない
- **ポイントが入るのは完了したときだけ**。受講中のぶんまで数えると「まだ終わっていないのに獲得済み」になる
- **同じ受講者・同じ講座の記録は 2 つ作れない**（複合ユニーク）。2 行あると完了なのか受講中なのかが決められない。受け直しは既存の記録を更新する
- **受講記録のある講座は削除できない**。使わなくなった講座は下書きに戻して選択肢から外す
- **`Enrollment::reconstitute()` は整合を通さない**。規則を後から足したときに過去の行が読めなくなり、一覧ごと開けなくなるのを防ぐ

## 取り込まなかったもの

| 対象 | 理由 |
| --- | --- |
| **チャット** | 参考元で唯一まともに動く機能だったが、docs-manager は業務管理ツールで、社内チャットは既存の手段（Slack 等）がある。自前で抱える維持コストが見合わない |
| **ユーザーの権限（`type`）** | docs-manager には権限の概念が一切無く、導入するとアプリ全体に影響する。まずは全員が講座を作れる・受講できる形にした。必要になったら別途 |
| **課題 (`tasks`)** | 参考元のテーブルが `id` と `timestamps` しか無く、提出・採点の設計を丸ごと起こす必要がある。講座と受講記録が固まってから |
| ユーザーの `username` / `disp_name` / `icon_image` | docs-manager の `users.name` で足りている |
| ホーム・個人設定の画面 | docs-manager に同等のものがある |
| `previous/`（Laravel 化以前のレガシー PHP） | 参考元のリポジトリに残っていた旧実装。現行の設計に影響しない |

## 引き継いだ画像アセット

移植元のリポジトリを整理するにあたり、docs-manager に無かった画像だけを退避したもの。

| ファイル | 中身 | 移植元 |
| --- | --- | --- |
| `public/favicon.ico` | Novalumo のファビコン (6.9KB) | `public/icons/favicon.ico` |
| `public/favicon.png` | 同じものの 496×496 版 (apple-touch-icon 用) | `public/icons/favicon.png` |
| `resources/img/novalumo.jpg` | ロゴ + タグライン "A New World Creation Company" (640×400) | `resources/img/novalumo.jpg` |
| `resources/img/mask.png` | 40×40 の単色グレー。移植元でも未使用で用途不明 | `resources/img/mask.png` |

**`favicon.ico` は docs-manager では長らく 0 バイトの空ファイルで、`app.blade.php` から参照もされていなかった。** 実体を入れたうえで `<link rel="icon">` を繋いである。

`novalumo.jpg` と `mask.png` は現時点でどこからも参照していない。

### かぶりとして持ってこなかったもの

| ファイル | 理由 |
| --- | --- |
| `logo.svg` | docs-manager の `resources/img/logo.svg` と**バイト単位で同一**（in-house-timecard-app から先に移植済み） |
| `logo.png` | 同じ Novalumo ワードマークの 936×204 ラスタ。docs-manager には既にベクタ (`logo.svg`) と、より高解像度のラスタ (`Logo.png`, 1444×245) がある。加えて macOS の大文字小文字を区別しないファイルシステムでは `Logo.png` と衝突する |
| `btn-open.svg` / `btn-close.svg` | ハンバーガーメニューの開閉アイコン。docs-manager は Bootstrap Icons の `bi-list` / `bi-x-lg` で同じ役割を満たしている |
| `previous/assets/img/*` | `resources/img/*` の完全な複製（6 ファイルすべて同一ハッシュ） |
| `previous/favicon.*` | `public/icons/favicon.*` の複製 |

## 残っている制約

- 課題（提出物）は無い
- 受講者ごとの実績を並べる画面は無い。受講記録の一覧に絞り込みと集計（完了件数・獲得ポイント）を出すところまで
- 講座に教材そのもの（動画・資料へのリンクなど）を持たせていない。いまは講座名と説明だけ
