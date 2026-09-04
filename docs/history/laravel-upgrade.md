# Laravel 8 → 13 の移行で直したこと

メジャーバージョンを 1 つずつ上げながら、レイヤー分割と Inertia 化を進めた。ここに並ぶのは**アップグレードで壊れたものではなく、元から壊れていたもの**。振る舞いが変わっているので、旧挙動を前提にした手順書があれば更新すること。

## 直した不具合

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

## フロントエンド移行で削除したもの

移行の完了と同時に消えた。

- **Alpine.js** と `resources/views/components/` の Blade コンポーネント一式（`resources/ts/components/ui/` に移植済み）
- **`layouts/default.blade.php` / `layouts/auth.blade.php`**（`Layouts/Default.svelte` / `Layouts/Auth.svelte` に移植済み）
- **`signup.blade.php`**（ルートが登録されておらず到達不能だった）
- **`lib/nfc-auth.ts` / `lib/metamask-auth.ts`**（`window` の `load` で `document.body` に DOM を組み立てていた。`NfcSignIn.svelte` / `MetamaskSignIn.svelte` に置き換え）
- **`window.novalumo`** と `types/globals.d.ts`（Blade の inline スクリプトから呼ぶ入口だった）
