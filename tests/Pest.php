<?php

use App\Models\Customer;
use App\Models\OrderHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature テストは Laravel の TestCase を通す。TestCase::setUp() で
| withoutVite() を呼んでいるため、テスト前に pnpm build しなくても
| @vite ディレクティブが解決できる。
|
| Unit テストはフレームワークを起動しない素の PHPUnit TestCase のまま。
|
*/

uses(TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
|
| DB を触る Feature テストはすべて sqlite の :memory: 上で動く
| (接続先は phpunit.xml で指定)。
|
*/

uses(RefreshDatabase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
|
| Pest ではテストファイル内で定義した関数もグローバルになるため、
| 複数のテストファイルで使うフィクスチャはここに集約する。
|
*/

/**
 * テスト用ユーザーを作る。
 */
function createUser(array $attributes = []): User
{
    return User::create(array_merge([
        'name' => 'テスト太郎',
        'email' => 'test@example.com',
        'password' => Hash::make('secret123'),
    ], $attributes));
}

/**
 * ログイン済みセッションを張ったテストケースを返す。
 *
 * このアプリは Illuminate\Auth を使わず session('user_id'/'name'/'email') を
 * 手で組み立てているため、actingAs ではなく withSession で再現する。
 */
function actingAsUser(User $user): TestCase
{
    return test()->withSession([
        'user_id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
    ]);
}

/**
 * 顧客を 1 件作る。Customer は $fillable が無いので属性を個別に代入する。
 */
function createCustomer(string $name = '株式会社テスト'): Customer
{
    $customer = new Customer();
    $customer->name = $name;
    $customer->is_company = 1;
    $customer->email = 'client@example.com';
    $customer->phone = '03-0000-0000';
    $customer->post_code = '100-0001';
    $customer->address = '千代田1-1';
    $customer->city = '千代田区';
    $customer->state = '東京都';
    $customer->country = '日本';
    $customer->note = null;
    $customer->save();

    return $customer;
}

/**
 * 発注書ヘッダーを 1 件作る。
 */
function createHeader(array $attributes = []): OrderHeader
{
    return OrderHeader::create(array_merge([
        'customer_id' => 1,
        'responsible' => '担当者',
        'honor_title' => '御中',
        'issued_date' => '2026-09-01',
        'exp_date' => '2026-09-30',
        'order_no' => 'NO-001',
        'title' => 'テスト発注',
        'subtotal_price' => 1000,
        'tax_price' => 100,
        'total_price' => 1100,
        'remarks' => '備考',
        'is_issued' => 0,
        'is_ordered' => 0,
        'is_deleted' => 0,
        'is_converted' => 0,
    ], $attributes));
}

/**
 * 発注書フォームの POST ペイロード。明細は配列で送られる。
 */
function orderPayload(array $overrides = []): array
{
    return array_merge([
        'customer_id' => 1,
        'responsible' => '担当者',
        'honor_title' => '御中',
        'issued_date' => '2026-09-01',
        'exp_date' => '2026-09-30',
        'order_no' => 'NO-001',
        'title' => 'テスト発注',
        'subtotal' => 1500,
        'taxTotal' => 150,
        'totalPrice' => 1650,
        'remarks' => '備考',
        'item_name' => ['商品A', '商品B'],
        'qty' => [1, 2],
        'unit' => ['個', '式'],
        'cost' => [1000, 250],
        'tax' => [1, 1],
        'price' => [1000, 500],
    ], $overrides);
}
