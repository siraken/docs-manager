<?php

use App\Models\Customer;
use App\Models\OrderDetail;
use App\Models\OrderHeader;

/**
 * 発注書 (OrderController) のリグレッションテスト。
 *
 * 論理削除が is_deleted カラムの手動運用であること、編集が
 * 「既存を物理削除して再作成」であることなど、フレームワーク標準から
 * 外れた挙動を固定する。
 */

beforeEach(function () {
    actingAsUser(createUser());
});

test('一覧には削除済みが含まれない', function () {
    createHeader(['order_no' => 'ALIVE-1']);
    createHeader(['order_no' => 'TRASHED-1', 'is_deleted' => 1]);

    $response = $this->get('/orders');

    $response->assertOk();
    $orders = $response->viewData('orders');
    expect($orders)->toHaveCount(1)
        ->and($orders[0]->order_no)->toBe('ALIVE-1');
});

test('ゴミ箱には削除済みだけが表示される', function () {
    createCustomer();
    createHeader(['order_no' => 'ALIVE-1']);
    createHeader(['order_no' => 'TRASHED-1', 'is_deleted' => 1]);

    $response = $this->get('/orders/trash');

    $response->assertOk();
    $orders = $response->viewData('orders');
    expect($orders)->toHaveCount(1)
        ->and($orders[0]->order_no)->toBe('TRASHED-1');
});

test('発注書を作成するとヘッダーと明細が保存される', function () {
    createCustomer();

    $response = $this->post('/orders/create', orderPayload());

    $response->assertRedirect('/orders');
    $response->assertSessionHas('flash_status', 'success');

    expect(OrderHeader::count())->toBe(1);

    $header = OrderHeader::first();
    expect($header->order_no)->toBe('NO-001')
        ->and((int) $header->subtotal_price)->toBe(1500)
        ->and((int) $header->tax_price)->toBe(150)
        ->and((int) $header->total_price)->toBe(1650)
        // 新規作成時は各フラグが 0 で初期化される
        ->and((int) $header->is_issued)->toBe(0)
        ->and((int) $header->is_ordered)->toBe(0)
        ->and((int) $header->is_deleted)->toBe(0);

    // 明細は slip_id (order_header_id ではない) で紐づく
    $details = OrderDetail::where('slip_id', $header->id)->get();
    expect($details)->toHaveCount(2)
        ->and($details[0]->item_name)->toBe('商品A')
        ->and((int) $details[0]->price)->toBe(1000)
        ->and($details[1]->item_name)->toBe('商品B');
});

test('品名が空の明細行は保存されない', function () {
    createCustomer();

    // 実際のフォームでは未入力行が空文字で送られ、
    // ConvertEmptyStringsToNull が null に変換した結果スキップされる
    $this->post('/orders/create', orderPayload([
        'item_name' => ['商品A', '', ''],
        'qty' => [1, '', ''],
        'unit' => ['個', '', ''],
        'cost' => [1000, '', ''],
        'tax' => [1, '', ''],
        'price' => [1000, '', ''],
    ]));

    $header = OrderHeader::first();
    expect(OrderDetail::where('slip_id', $header->id)->get())->toHaveCount(1);
});

test('編集は既存を物理削除して作り直すためIDが変わる', function () {
    $this->markTestIncomplete(
        'OrderController::edit() は $req[\'is_issued\'] 等を参照するが、'
        . 'orders/form.blade.php に該当する入力が存在しないため Undefined index で 500 になる。'
        . '発注書の編集は現状保存できない。修正後にこの markTestIncomplete を外すこと。'
    );

    createCustomer();
    $original = createHeader(['order_no' => 'OLD-NO']);
    OrderDetail::create([
        'slip_id' => $original->id,
        'item_name' => '旧商品',
        'quantity' => 1,
        'unit' => '個',
        'cost' => 100,
        'tax_id' => 1,
        'price' => 100,
    ]);

    $response = $this->post('/orders/edit/' . $original->id, orderPayload(['order_no' => 'NEW-NO']));

    $response->assertRedirect('/orders');

    // 元のレコードは物理削除される
    expect(OrderHeader::find($original->id))->toBeNull()
        ->and(OrderDetail::where('slip_id', $original->id)->get())->toHaveCount(0)
        ->and(OrderHeader::count())->toBe(1);

    // 新しい ID で作り直される
    $recreated = OrderHeader::first();
    expect($recreated->id)->not->toBe($original->id)
        ->and($recreated->order_no)->toBe('NEW-NO')
        ->and(OrderDetail::where('slip_id', $recreated->id)->get())->toHaveCount(2);
});

test('削除はis_deletedを1にするだけで行は残る', function () {
    $header = createHeader();

    $this->get('/orders/delete/' . $header->id)->assertRedirect('/orders');

    expect(OrderHeader::count())->toBe(1)
        ->and((int) OrderHeader::find($header->id)->is_deleted)->toBe(1);
});

test('復元でis_deletedが0に戻る', function () {
    $header = createHeader(['is_deleted' => 1]);

    $this->get('/orders/restore/' . $header->id)->assertRedirect('/orders');

    expect((int) OrderHeader::find($header->id)->is_deleted)->toBe(0);
});

test('詳細画面が表示できる', function () {
    $this->markTestIncomplete(
        'ルート orders.view は登録されているが OrderController::view() が存在せず 500 になる。'
        . '一覧にリンクが無いため UI からは到達しないが、ルートは生きている。'
        . 'メソッド追加後にこの markTestIncomplete を外すこと。'
    );

    $customer = createCustomer();
    $header = createHeader(['customer_id' => $customer->id]);
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
});
