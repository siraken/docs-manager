# テスト

Pest 4 + PHPUnit 12。ドメイン層は Laravel を起動せずに Unit で書ける。

## 走らせ方

**Pest 4**。`artisan test` も Pest を使う。

```bash
./vendor/bin/pest                              # 全件
./vendor/bin/pest tests/Feature/AuthTest.php   # ファイル指定
./vendor/bin/pest --filter 'ログイン'           # 名前で絞る
just test                                      # ホスト (sqlite)
just sail-test                                 # Sail 経由 (MySQL)
```

## 書き方

テストは Pest の関数記法（`test()` / `beforeEach()` / `expect()`）で書く。

**共通のフィクスチャは `tests/Pest.php` に置く**。Pest ではテストファイル内で定義した関数もグローバルスコープに入るため、複数ファイルで同名の関数を定義すると再宣言エラーになる。`createUser()` / `actingAsUser()` / `createCustomer()` / `createHeader()` / `orderPayload()` / `walletAddress()` がここにある。**この制約は Unit テストにも及ぶ**ので、テストファイル内でヘルパを定義するときは他ファイルと衝突しない名前にすること（例: `tests/Unit/OrderTest.php` の `newOrder()` / `orderLine()`）。

フィクスチャは Eloquent モデルを直接使う。ユースケース経由で用意すると「準備」と「検証対象」が同じ経路になり、リグレッションを検出できなくなる。

`uses(TestCase::class)->in('Feature')` と `uses(RefreshDatabase::class)->in('Feature')` も `tests/Pest.php` で設定している。

## 画面のテスト

**画面のテストは `viewData()` ではなく props を見る**。`viewData()` は Blade のビューにしか使えず、画面はすべて Inertia になっている。

```php
$this->get('/orders')->assertInertia(fn (AssertableInertia $page) => $page
    ->component('Orders/Index')
    ->has('orders', 1)
    ->where('orders.0.orderNo', 'ALIVE-1'));
```

`config/inertia.php` で `ensure_pages_exist` を有効にしてあるので、`component()` はページの実在も確認する（`resources/ts/Pages` を探す）。ページ名の打ち間違いがテストで落ちる。

## Unit と Feature の使い分け

**ドメイン層は Laravel に依存しないので Unit テストで書ける**。`tests/Unit/` にある Money / TaxRate / ProjectStatus / OrderNo / TwoFactorSecret / Order / TravelExpense のテストはフレームワークを起動せずに動く。金額計算やステータス遷移のような業務ルールはここに書き、HTTP の配線は Feature に書く。

## sqlite で回している都合

**PHPUnit 12**（12.5.33）。`phpunit.xml` で `DB_CONNECTION=sqlite` / `DB_DATABASE=:memory:` を指定しているため、テストは MySQL を必要とせず Sail を起動しなくても回る。DB を使うテストは `RefreshDatabase` を付ける。設定は PHPUnit 10 で入った形式（`<coverage>` ではなく `<source>`）のままで 12 でもそのまま通る。

`phpunit.xml` の `xsi:noNamespaceSchemaLocation` は `10.5` を指したままだが、**PHPUnit 12 はこれを警告しない**（スキーマの参照先はエディタ向けで、PHPUnit 自身は使わない）。動かないわけではないので、慌てて直さなくてよい。

**sqlite と MySQL の型差異に注意**: sqlite (PDO) は integer カラムを文字列で返す。`$header->total_price` は `'1100'` であって `1100` ではないため、テストで数値比較する際は `(int)` にキャストする。アプリ側では Mapper（`app/Infrastructure/Persistence/Eloquent/Mapper/`）がドメインへ移す際に必ず型を寄せているので、この差異がドメイン層まで漏れることはない。
