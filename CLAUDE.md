# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## プロジェクト概要

Novalumo 社内向けの業務管理ツール（発注書・出張申請・出張旅費精算・案件管理・顧客管理・勤務報告・契約管理・仕訳帳・社内研修・チャット）。Laravel 13 + Tailwind CSS v4。**UI もコード内コメントも日本語**で書く。

- **画面は全 38 枚が Inertia + Svelte 5**。Blade として残っているのはルートテンプレート (`app.blade.php`)、エラーページ (`errors/`)、メール本文 (`emails/`) だけ
- **サーバー側は Laravel の既定 (`app/Http`) の内側に Domain / Application / Infrastructure を足した構成**。以前はコントローラに DB アクセス・金額計算・PDF 描画・外部 API 呼び出しが直書きされていた
- Laravel 8 から 13 へメジャーバージョンを 1 つずつ上げてきた（1 メジャー = 1 PR）。**現在 13 で、アップグレードは完了している**

## ドキュメント

詳細はここには書かず、`docs/` に種類ごとに置いてある。**作業を始める前に、関係する回を開くこと。**

| ドキュメント | 中身 |
| --- | --- |
| [開発環境とコマンド](docs/development.md) | nix devShell、`just` のレシピ、DB を立てずに画面を見る方法、PHP バージョンの注意 |
| [アーキテクチャ](docs/architecture.md) | レイヤー構成と依存の向き、`bootstrap/app.php` の設定、このアプリ特有の癖（認証・論理削除・PDF・外部連携） |
| [フロントエンド](docs/frontend.md) | Inertia + Svelte 5 の構成、ディレクトリ、Vite / Tailwind、ViewModel、書くときの約束 |
| [テスト](docs/testing.md) | Pest の書き方、フィクスチャの置き場所、Inertia のアサーション、sqlite と MySQL の型差異 |
| [CI とデプロイ](docs/ci.md) | ワークフローの中身、デプロイを作り直す場合の注意 |
| [残っている TODO](docs/todo.md) | 未実装のまま残っている重いもの（2FA の未接続、MetaMask ログインの脆弱性など） |
| [Laravel 8 → 13 の移行で直したこと](docs/history/laravel-upgrade.md) | 元から壊れていた箇所と、その直し方 |
| [in-house-timecard-app からの移植](docs/history/timecard-port.md) | 契約管理・勤務報告の移植、かぶり機能から取り込んだ仕様、仕訳帳の新規開発 |
| [e-learning を参考にした社内研修](docs/history/e-learning-port.md) | 講座・受講記録の新規開発と、取り込まなかったものの判断 |

## 絶対に守ること

詳細と背景は上のドキュメントにある。ここは「知らずに破ると壊れるもの」だけを並べている。

### レイヤー

依存は**外側から内側への一方向**。ドメイン層は Laravel も Eloquent も知らない。

- **ドメイン層 (`app/Domain`) に `use Illuminate\...` を書かない**。書きたくなったらそれは Infrastructure の関心
- **コントローラに業務ロジックを書かない**。`app/Http` は Laravel の既定の場所で、ディレクトリ名では守られていない。**メソッドが 10 行を超えたらユースケース側の仕事**
- **Eloquent モデルをリポジトリの外に出さない**。画面へはドメインのエンティティでもなく `app/Http/ViewModels/` の ViewModel を渡す
- **ユースケースはコンストラクタでインターフェースを受け取る**。テストで `$this->app->instance(...)` して差し替えられるように
- **依存注入はメソッドインジェクション**。コントローラのコンストラクタに並べると、1 アクションのために全部が解決される

**新しい機能を足すときの流れ**:

1. 業務ルールがあるなら `Domain` にエンティティ / 値オブジェクトを置く（テストは `tests/Unit/`）
2. 手順を `Application/<文脈>/UseCase/` に 1 クラスで書く。入力は `Input/` の DTO
3. DB や外部サービスに触るなら、先に `Domain/.../Repository/` か `Application/.../Port/` にインターフェースを置き、実装を `Infrastructure` に書く
4. `DomainServiceProvider::$bindings` に「インターフェース => 実装」を 1 行足す
5. `app/Http` に FormRequest とコントローラのメソッドを足し、`routes/web.php` に登録する

### サーバー側

- **認証は `Illuminate\Auth` ではなく素のセッション**。保護したいルートは `Route::middleware('login')`（`auth` ではない）。ログインユーザーは `session('user_id')` / `session('name')` で、`Auth::user()` は機能しない
- **フォーム表示と保存はコントローラのメソッドを分ける**。ルート名は GET 側にだけ付ける
- **フラッシュは `App\Support\Flash` を使う**。3 キーを手で書かない
- **`config/services.php` を通す**。`env()` を直接読むと `config:cache` した環境で null になる
- **ドメイン例外は catch しない**。`bootstrap/app.php` の `withExceptions()` が 404 / リダイレクトに落とす

### フロントエンド

- **Svelte 5 は runes で書く**（`$state` / `$derived` / `$effect`）
- **画面から参照する URL はサーバー側で組む**。Ziggy のようなルートヘルパは入れていない
- **ViewModel は `JsonSerializable` を実装する**。PHP 側の `jsonSerialize()` と `resources/ts/lib/*-types.ts` は対なので、片方を変えたらもう片方も直す
- **内部の画面へのリンクは Inertia 遷移にする**。PDF / CSV のダウンロードは必ず `external`
- **日付や連番の既定値はサーバーで決める**。画面が `new Date()` を持つとテストから固定できない

### 仕訳帳

- **仕訳の金額は 1 つしか持たない**。借方と貸方で必ず同じ額が計上されるので、貸借がずれた帳簿は表現できない
- **勘定科目は `accounts` マスタから id で参照する**。文字列で持つと表記ゆれで集計が壊れる
- **使われている勘定科目は削除できない**。使わなくなったものは無効化して選択肢から外す
- **残高がどちらの側に立つかは `AccountType` が決める**。画面やクエリで judge しない

### 社内研修

- **受講記録の状態と日付は必ず噛み合う**。未受講は日付を持たず、完了には完了日が要る。整合は `Enrollment` が調整するので、画面やクエリで辻褄を合わせない
- **ポイントが入るのは完了したときだけ**。受講中のぶんを数えない
- **同じ受講者・同じ講座の記録は 2 つ作らない**。受け直しは既存の記録を更新する

### チャット

- **投稿者はクライアントから受け取らない**。ログイン中のユーザーをサーバーが入れる（送らせると他人になりすませる）
- **消せるのは自分の発言だけ**
- **更新は Inertia の部分リロード**（`router.reload({ only: ['messages'] })`）。取得用の JSON エンドポイントは作らない

### テスト

- **共通のフィクスチャは `tests/Pest.php` に置く**。Pest はテストファイル内の関数もグローバルにするため、同名の関数を複数ファイルで定義すると再宣言エラーになる
- **フィクスチャは Eloquent モデルを直接使う**。ユースケース経由にすると「準備」と「検証対象」が同じ経路になる
- **画面のテストは `assertInertia()` で props を見る**（`viewData()` は使えない）
- **sqlite は integer カラムを文字列で返す**。数値比較は `(int)` にキャストする

### 出す前に

**`just check` を通す**（Pest → `tsc --noEmit` → `svelte-check`）。**型エラーは現在 0 件なので、増やしたまま放置しない。**

## コーディング規約

- StyleCI: `laravel` preset（`no_unused_imports` は無効化）
- インデント: PHP/その他 4 スペース、JS/TS/JSX/YAML 2 スペース（`.editorconfig`）
- コミットメッセージは `add:` / `feat:` / `fix:` などの小文字プレフィックス（Git hook による自動付与は無し）
