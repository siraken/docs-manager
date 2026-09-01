<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\OrderDetail;
use App\Models\OrderHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * 発注書 (OrderController) のリグレッションテスト。
 *
 * 論理削除が is_deleted カラムの手動運用であること、編集が
 * 「既存を物理削除して再作成」であることなど、フレームワーク標準から
 * 外れた挙動を固定する。
 */
class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->withSession([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    private function createCustomer(string $name = '株式会社テスト'): Customer
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

    private function createHeader(array $attributes = []): OrderHeader
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

    private function orderPayload(array $overrides = []): array
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

    public function test_一覧には削除済みが含まれない(): void
    {
        $this->createHeader(['order_no' => 'ALIVE-1']);
        $this->createHeader(['order_no' => 'TRASHED-1', 'is_deleted' => 1]);

        $response = $this->get('/orders');

        $response->assertOk();
        $orders = $response->viewData('orders');
        $this->assertCount(1, $orders);
        $this->assertSame('ALIVE-1', $orders[0]->order_no);
    }

    public function test_ゴミ箱には削除済みだけが表示される(): void
    {
        $this->createCustomer();
        $this->createHeader(['order_no' => 'ALIVE-1']);
        $this->createHeader(['order_no' => 'TRASHED-1', 'is_deleted' => 1]);

        $response = $this->get('/orders/trash');

        $response->assertOk();
        $orders = $response->viewData('orders');
        $this->assertCount(1, $orders);
        $this->assertSame('TRASHED-1', $orders[0]->order_no);
    }

    public function test_発注書を作成するとヘッダーと明細が保存される(): void
    {
        $this->createCustomer();

        $response = $this->post('/orders/create', $this->orderPayload());

        $response->assertRedirect('/orders');
        $response->assertSessionHas('flash_status', 'success');

        $this->assertSame(1, OrderHeader::count());
        $header = OrderHeader::first();
        $this->assertSame('NO-001', $header->order_no);
        $this->assertSame(1500, (int) $header->subtotal_price);
        $this->assertSame(150, (int) $header->tax_price);
        $this->assertSame(1650, (int) $header->total_price);
        // 新規作成時は各フラグが 0 で初期化される
        $this->assertSame(0, (int) $header->is_issued);
        $this->assertSame(0, (int) $header->is_ordered);
        $this->assertSame(0, (int) $header->is_deleted);

        // 明細は slip_id (order_header_id ではない) で紐づく
        $details = OrderDetail::where('slip_id', $header->id)->get();
        $this->assertCount(2, $details);
        $this->assertSame('商品A', $details[0]->item_name);
        $this->assertSame(1000, (int) $details[0]->price);
        $this->assertSame('商品B', $details[1]->item_name);
    }

    public function test_品名が空の明細行は保存されない(): void
    {
        $this->createCustomer();

        // 実際のフォームでは未入力行が空文字で送られ、
        // ConvertEmptyStringsToNull が null に変換した結果スキップされる
        $this->post('/orders/create', $this->orderPayload([
            'item_name' => ['商品A', '', ''],
            'qty' => [1, '', ''],
            'unit' => ['個', '', ''],
            'cost' => [1000, '', ''],
            'tax' => [1, '', ''],
            'price' => [1000, '', ''],
        ]));

        $header = OrderHeader::first();
        $this->assertCount(1, OrderDetail::where('slip_id', $header->id)->get());
    }

    public function test_編集は既存を物理削除して作り直すためIDが変わる(): void
    {
        $this->markTestIncomplete(
            'OrderController::edit() は $req[\'is_issued\'] 等を参照するが、'
            . 'orders/form.blade.php に該当する入力が存在しないため Undefined index で 500 になる。'
            . '発注書の編集は現状保存できない。修正後にこの markTestIncomplete を外すこと。'
        );

        $this->createCustomer();
        $original = $this->createHeader(['order_no' => 'OLD-NO']);
        OrderDetail::create([
            'slip_id' => $original->id,
            'item_name' => '旧商品',
            'quantity' => 1,
            'unit' => '個',
            'cost' => 100,
            'tax_id' => 1,
            'price' => 100,
        ]);

        $response = $this->post('/orders/edit/' . $original->id, $this->orderPayload([
            'order_no' => 'NEW-NO',
        ]));

        $response->assertRedirect('/orders');

        // 元のレコードは物理削除される
        $this->assertNull(OrderHeader::find($original->id));
        $this->assertCount(0, OrderDetail::where('slip_id', $original->id)->get());

        // 新しい ID で作り直される
        $this->assertSame(1, OrderHeader::count());
        $recreated = OrderHeader::first();
        $this->assertNotSame($original->id, $recreated->id);
        $this->assertSame('NEW-NO', $recreated->order_no);
        $this->assertCount(2, OrderDetail::where('slip_id', $recreated->id)->get());
    }

    public function test_削除はis_deletedを1にするだけで行は残る(): void
    {
        $header = $this->createHeader();

        $response = $this->get('/orders/delete/' . $header->id);

        $response->assertRedirect('/orders');
        $this->assertSame(1, OrderHeader::count());
        $this->assertSame(1, (int) OrderHeader::find($header->id)->is_deleted);
    }

    public function test_復元でis_deletedが0に戻る(): void
    {
        $header = $this->createHeader(['is_deleted' => 1]);

        $response = $this->get('/orders/restore/' . $header->id);

        $response->assertRedirect('/orders');
        $this->assertSame(0, (int) OrderHeader::find($header->id)->is_deleted);
    }

    public function test_詳細画面が表示できる(): void
    {
        $this->markTestIncomplete(
            'ルート orders.view は登録されているが OrderController::view() が存在せず 500 になる。'
            . '一覧にリンクが無いため UI からは到達しないが、ルートは生きている。'
            . 'メソッド追加後にこの markTestIncomplete を外すこと。'
        );

        $customer = $this->createCustomer();
        $header = $this->createHeader(['customer_id' => $customer->id]);
        OrderDetail::create([
            'slip_id' => $header->id,
            'item_name' => '商品A',
            'quantity' => 1,
            'unit' => '個',
            'cost' => 1000,
            'tax_id' => 1,
            'price' => 1000,
        ]);

        $this->get('/orders/view/' . $header->id)->assertOk();
    }
}
