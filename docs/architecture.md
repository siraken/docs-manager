# アーキテクチャ

Laravel の既定 (`app/Http`) の内側に Domain / Application / Infrastructure を足した構成と、このアプリ特有の癖。画面側は [フロントエンド](frontend.md) を参照。

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

## Laravel 13 で入れた設定

- **CSRF ミドルウェアは `PreventRequestForgery`**: Laravel 13 で `VerifyCsrfToken` からリネームされ、`Sec-Fetch-Site` ヘッダによるリクエスト元検証が加わった。**継承した独自クラスは持っておらず**、web グループにフレームワーク標準の `Illuminate\Foundation\Http\Middleware\PreventRequestForgery` がそのまま入っている（明示的に名前を書いているのは `config/sanctum.php` の `validate_csrf_token` だけ）。`VerifyCsrfToken` / `ValidateCsrfToken` は非推奨エイリアスとして残っているが使わないこと
- **`handle()` は `hasValidOrigin()` を `tokensMatch()` より先に評価する**。`Sec-Fetch-Site: same-origin` が付いていればトークンを見ずに通す。そのためブラウザからの同一オリジン fetch は CSRF トークンが無くても 200 になり、**ブラウザ操作だけではトークン検証の経路を確認できない**。検証したいときは `Sec-Fetch-Site` を送らない curl を使うこと（トークン無し・不正なら 419 になる）
- **`config/session.php` の `serialization` は `json`**: PHP の unserialize による gadget chain 攻撃を避けるため。このアプリはセッションに `user_id` / `name` / `email` の文字列しか入れていないので json で足りる
- **`config/cache.php` の `serializable_classes` は `false`**: キャッシュから PHP オブジェクトを復元しない設定。キャッシュにオブジェクトを入れていないため false のままでよい
- **`composer.json` の `allow-plugins` に `pestphp/pest-plugin`**: composer 2.2 以降はプラグインの実行に明示的な許可が要る。増やすときは必要最小限にする

## アーキテクチャ上の重要な癖

Laravel の作法から外れている箇所。知らずに触ると壊れる。

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
- **Jira**: API は叩かない。案件の `jira_key` からリンクを組むための URL だけを `config('services.jira.browse_url')`（`JIRA_BROWSE_URL`）に持つ。組み立てるのは `ProjectView` で、ドメイン層はホスト名を知らない
- **CSV インポート**: `SplFileObject` + `READ_CSV` で読む（`Infrastructure\Csv\SplFileObjectCsvReader`）。フラグの組み合わせは移行前と同じ。**列は 0 始まりではなく `$row[1]` から読む**（先頭列は使われない）という癖もそのまま
- **CSV の取り込みは 1 トランザクション**。1 行でも日付として解釈できない行があれば全体を取り消す（移行前は 1 行ずつ保存していたため、途中で失敗すると半端に入った）
