# in-house-timecard-app からの移植

`novalumo/in-house-timecard-app`（Laravel 10 + Bootstrap 4 の勤怠ツール）から取り込んだもの。**機能まるごとの移植は 2 つ**、**かぶり機能から仕様だけ取り込んだものが 3 つ**ある。

| timecard の機能 | docs-manager | 扱い |
| --- | --- | --- |
| **Contract（契約管理）** | — | **機能ごと移植** |
| **Report（勤務報告）** | — | **機能ごと移植** |
| Client（クライアント） | Customer（顧客） | かぶり。`person`（担当者）だけ取り込み |
| Project（プロジェクト） | Project（案件） | かぶり。`pid`（Jira キー）だけ取り込み |
| Company（会社情報） | Setting / CompanyProfile | かぶり。会社概要 4 項目だけ取り込み |
| User | User | かぶり。docs-manager が上位互換のため何も取り込まない |

**移植価値が無いと判断したもの**: `clients.flag`（フォームに入力欄はあるが読む処理がどこにも無い）、`welcome.blade.php`（中身が空）、`signin.blade.php`（docs-manager は独自セッション認証 + NFC / MetaMask を実装済み）、ナビの「会計 / 見積書 / 請求書 / 簡易料金計算 / フィードバック / バックアップ」（`public_path()` を href に埋めた壊れたリンクで実体が無い）。

機能ごとの移植は 2 コミットに分けてある。1 つ目が移植元のファイルを無加工でコピーしたもの、2 つ目がこのリポジトリの構成へ寄せたもの。**移植元との差分を読みたいときは 2 つ目のコミットの diff を見ること。**

## スキーマの変更

移植元のテーブルはそのままでは使えなかったので作り直している。**どちらの表も docs-manager では新規テーブルなので、移行用のマイグレーションは無い。**

- **取引先は `customers` を参照する**。移植元の `client_id` は参照先の無い整数だった。カラム名も `customer_id` に揃えている（`order_headers` と同じ）
- **`reports.project_id` を足した**。移植元の登録フォームには案件のセレクトがあったが `name` 属性が空で送信されず、テーブルにも列が無かった
- **`reports.work_time`（float の時間）を `work_minutes`（整数の分）にした**。`Money` が円を整数で持つのと同じ理由
- **`contracts.contract_id` を `contract_no` に改名**。主キーと紛らわしく、実体は「契約番号」だった。移植元のフォームが送っていた `pid` はテーブルに無いカラムで、値は静かに捨てられていた

## ドメインルール

- **勤務時間は始業・終業が揃っていればそこから計算する**（`Domain\Report\Entity\Report`）。フォームの申告値を使うのは時刻が片方でも欠けているときだけ。発注書の金額をサーバー側で計算し直しているのと同じ考え方
- **日跨ぎの勤務は 24 時間を足して扱う**（`TimeOfDay::minutesUntil()`）。22:00 出社 - 02:00 退社で負の勤務時間にならないようにするため
- **契約の状態はカラムとして持たない**。`ContractTerm::statusOn()` が契約期間と基準日から「開始前 / 契約中 / 終了」を導く。境界は両端とも含む
- **`Report::reconstitute()` は勤務時間を計算し直さない**。保存済みの値をそのまま採る（再計算すると、休憩控除のような規則を後から足したときに過去の記録まで遡って書き換わる）

## 移植時に直した不具合

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

## かぶり機能から取り込んだ仕様

機能そのものは移植しないが、timecard 側にしか無かった項目を docs-manager に足したもの。

### 案件の Jira キー (`projects.jira_key`)

`related_task_id` を置き換えた。あの列は**二重に死んでいた**。

- 指す先の `tasks` テーブルはマイグレーションごと存在しない（`App\Models\Task` を削除したときに判明している）
- Domain から TypeScript の型まで全層を通っているのに、フォームにも一覧にも出ないため値を入れる手段が無かった

timecard の `projects.pid` は同じ「案件に紐づく外部の識別子」でありながら、一覧から Jira へリンクする導線として実際に使われていた。整数では `NOVA-123` を持てないので文字列の `jira_key` に入れ替えている。

- 書式の検証は `Domain\Project\ValueObject\JiraKey`。`NOVA` と `NOVA-123` のどちらも受け、小文字は大文字に寄せる（Jira 自身の既定に合わせる）
- **リンクの URL はドメイン層で組まない**。ホスト名は環境設定なので `config('services.jira.browse_url')`（`JIRA_BROWSE_URL`）から読み、`ProjectView` が組み立てる。移植元はビューに直書きしていた
- 一覧の Jira リンクは**外部サイトなので Inertia 遷移にしない**（`target="_blank"` の素のリンク）

### 顧客の担当者 (`customers.person`)

docs-manager は担当者を発注書ごと（`order_headers.responsible`）にしか持っておらず、取引先を選んでも毎回手入力していた。マスタに既定の担当者を持たせ、**発注書フォームで取引先を選ぶと担当者欄が埋まる**ようにしてある。

- 埋めるのは「担当者欄が空のとき」と「直前に選んでいた取引先の担当者がそのまま入っているとき」だけ。手で打った名前は消さない
- `CustomerView::options()` の戻り値に `person` を足してある。契約・勤務報告のセレクトでは読まないが、短い文字列 1 つなので `options()` を分けずに 1 本のままにしている

### 自社情報の会社概要 (`settings.name_en` / `established` / `capital` / `bank`)

- **`bank`（振込先）が本命**。移植前の発注書 PDF には振込先の記載が一切無く、別途伝える運用だった。備考欄の下（y=215〜）に印字する。**未設定なら欄ごと出さない**ので、設定していない環境の PDF は従来どおり
- `capital` は円の整数（`Money`）。`name_en` / `established` は会社概要の表示用
- 既存の `settings` 行を壊さないよう、足した列はいずれも nullable

## ついでに直した docs-manager 側の弱点

timecard 由来ではないが、上の作業で同じファイルを触るため一緒に直したもの。

| 症状 | 直した内容 |
| --- | --- |
| 案件フォームの取引先が顧客 ID の手打ち | `customers` のセレクトにした。`exists:customers,id` で存在しない ID も弾く |
| 案件一覧の取引先欄に生の ID が出る | サーバー側で顧客名を解決して渡す（`OrderController` と同じ N+1 回避の対応表） |
| 案件の備考が入力できない | `description` は FormRequest・Input・Entity まで通っているのに、フォームに入力欄が無かった |

## 移植していないもの

- **Bootstrap 4 のビュー**。画面は Inertia + Svelte で書き直した
- **`maatwebsite/excel` による Excel 出力**。移植元でも呼び出し箇所が無く、依存として宣言されているだけだった
- **`app/Models/`**。Eloquent モデルは `app/Infrastructure/Persistence/Eloquent/Models/` に置く規約に合わせた
- **`SimpleAuth` ミドルウェア**。中身が `// TODO: implement` でコメントアウトされており、実質何もしていなかった。認証は既存の `LoginMiddleware` に任せる
- **ページネーション**。一覧は年月で絞り込むので、1 か月分が上限になる。集計と表示の対象がずれない利点のほうが大きい
