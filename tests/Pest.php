<?php

use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use App\Infrastructure\Persistence\Eloquent\Models\OrderHeader;
use App\Infrastructure\Persistence\Eloquent\Models\User;
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
| ドメイン層は Laravel に依存しないので、その多くは Unit で書ける。
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
| フィクスチャは Eloquent モデルを直接使う。ユースケース経由にすると
| 「準備」と「検証対象」が同じ経路になり、リグレッションを検出できなくなるため。
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
 * 顧客を 1 件作る。
 */
function createCustomer(string $name = '株式会社テスト'): Customer
{
    return Customer::create([
        'name' => $name,
        'is_company' => 1,
        'email' => 'client@example.com',
        'phone' => '03-0000-0000',
        'post_code' => '100-0001',
        'address' => '千代田1-1',
        'city' => '千代田区',
        'state' => '東京都',
        'country' => '日本',
        'note' => null,
    ]);
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
 *
 * 金額 (price[]) は送っても使われない。保存される金額はサーバー側で
 * 数量・単価・税区分から計算し直すため。
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
        'remarks' => '備考',
        'item_name' => ['商品A', '商品B'],
        'qty' => [1, 2],
        'unit' => ['個', '式'],
        'cost' => [1000, 250],
        'tax' => [1, 1],
    ], $overrides);
}

/**
 * 有効な形式のウォレットアドレス (0x + 40 桁)。
 */
function walletAddress(string $suffix = '1'): string
{
    return '0x' . str_pad($suffix, 40, '0', STR_PAD_LEFT);
}
