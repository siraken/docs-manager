<?php

use App\Infrastructure\Persistence\Eloquent\Models\OrderDetail;
use App\Infrastructure\Persistence\Eloquent\Models\OrderHeader;
use Inertia\Testing\AssertableInertia;

/**
 * 発注書のリグレッションテスト。
 *
 * 論理削除が is_deleted カラムの手動運用であることなど、フレームワーク標準から
 * 外れた挙動を固定する。金額の計算がサーバー側に移ったこと、編集で id が
 * 変わらなくなったことも、ここで押さえている。
 *
 * 画面は Inertia + Svelte なので、描画結果ではなく Svelte へ渡る props を見る
 * (viewData() は Blade のビューにしか使えない)。
 */

beforeEach(function () {
    actingAsUser(createUser());
});

test('一覧には削除済みが含まれない', function () {
    createHeader(['order_no' => 'ALIVE-1']);
    createHeader(['order_no' => 'TRASHED-1', 'is_deleted' => 1]);

    $this->get('/orders')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Orders/Index')
            ->has('orders', 1)
            ->where('orders.0.orderNo', 'ALIVE-1'));
});

test('is_deletedがNULLの行も一覧に出る', function () {
    // 移行前の where('is_deleted', '!=', 1) は SQL の NULL 比較の都合で
    // NULL 行を落としていた。
    createHeader(['order_no' => 'NULL-FLAG', 'is_deleted' => null]);

    $this->get('/orders')->assertInertia(fn (AssertableInertia $page) => $page
        ->has('orders', 1)
        ->where('orders.0.orderNo', 'NULL-FLAG'));
});

test('ゴミ箱には削除済みだけが表示される', function () {
    createCustomer();
    createHeader(['order_no' => 'ALIVE-1']);
    createHeader(['order_no' => 'TRASHED-1', 'is_deleted' => 1]);

    $this->get('/orders/trash')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Orders/Trash')
            ->has('orders', 1)
            ->where('orders.0.orderNo', 'TRASHED-1'));
});

test('発注書を作成するとヘッダーと明細が保存される', function () {
    createCustomer();

    $response = $this->post('/orders/create', orderPayload());

    $response->assertRedirect('/orders');
    $response->assertSessionHas('flash_status', 'success');

    expect(OrderHeader::count())->toBe(1);

    // 小計 = 1×1000 + 2×250 = 1500、消費税 10% = 150、合計 = 1650
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
        // order_details.price は税込。1000 + 10% = 1100
        ->and((int) $details[0]->price)->toBe(1100)
        ->and($details[1]->item_name)->toBe('商品B')
        // 2 × 250 = 500、+10% で 550
        ->and((int) $details[1]->price)->toBe(550);
});

test('合計金額はフォームの申告ではなく明細から計算される', function () {
    createCustomer();

    // 画面が送ってくる合計欄を故意に食い違わせても、保存されるのは
    // 明細から計算した値になる。
    $this->post('/orders/create', orderPayload([
        'subtotal' => 999999,
        'taxTotal' => 999999,
        'totalPrice' => 999999,
    ]));

    $header = OrderHeader::first();
    expect((int) $header->subtotal_price)->toBe(1500)
        ->and((int) $header->tax_price)->toBe(150)
        ->and((int) $header->total_price)->toBe(1650);
});

test('税区分ごとに消費税が変わる', function () {
    createCustomer();

    $this->post('/orders/create', orderPayload([
        'item_name' => ['標準税率', '軽減税率', '対象外'],
        'qty' => [1, 1, 1],
        'unit' => ['個', '個', '個'],
        'cost' => [1000, 1000, 1000],
        'tax' => [1, 2, 5], // 10% / 軽減8% / 対象外
    ]));

    $header = OrderHeader::first();
    expect((int) $header->subtotal_price)->toBe(3000)
        ->and((int) $header->tax_price)->toBe(180) // 100 + 80 + 0
        ->and((int) $header->total_price)->toBe(3180);
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
    ]));

    $header = OrderHeader::first();
    expect(OrderDetail::where('slip_id', $header->id)->get())->toHaveCount(1);
});

test('編集しても発注書のIDは変わらない', function () {
    // 移行前は「既存を物理削除して作り直す」実装で id が変わっていた。
    // さらにフォームに存在しない is_issued 等を参照していたため、POST すると
    // Undefined array key で 500 になり、そもそも保存できなかった。
    createCustomer();
    $original = createHeader(['order_no' => 'OLD-NO']);
    OrderDetail::create([
        'slip_id' => $original->id,
        'item_name' => '旧商品',
        'quantity' => 1,
        'unit' => '個',
        'cost' => 100,
        'tax_id' => 1,
        'price' => 110,
    ]);

    $response = $this->post('/orders/edit/' . $original->id, orderPayload(['order_no' => 'NEW-NO']));

    $response->assertRedirect('/orders');

    expect(OrderHeader::count())->toBe(1);

    $updated = OrderHeader::first();
    expect($updated->id)->toBe($original->id)
        ->and($updated->order_no)->toBe('NEW-NO');

    // 明細は洗い替えされる
    $details = OrderDetail::where('slip_id', $original->id)->get();
    expect($details)->toHaveCount(2)
        ->and($details->pluck('item_name')->all())->toBe(['商品A', '商品B']);
});

test('編集ではフォームが持たない項目が維持される', function () {
    // 発行・受注ステータス、ごみ箱フラグ、社内メモはフォームに入力欄が無い。
    // 更新でこれらが 0 に潰れないこと。
    createCustomer();
    $original = createHeader([
        'is_issued' => 1,
        'is_ordered' => 2,
        'is_converted' => 1,
        'note' => '社内メモ',
    ]);

    $this->post('/orders/edit/' . $original->id, orderPayload());

    $updated = OrderHeader::find($original->id);
    expect((int) $updated->is_issued)->toBe(1)
        ->and((int) $updated->is_ordered)->toBe(2)
        ->and((int) $updated->is_converted)->toBe(1)
        ->and($updated->note)->toBe('社内メモ');
});

test('存在しない発注書の編集は404になる', function () {
    $this->get('/orders/edit/999')->assertNotFound();
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
    // 移行前はルートだけあってメソッドが無く 500、ビューは CakePHP のままだった。
    $customer = createCustomer();
    $header = createHeader(['customer_id' => $customer->id]);
    OrderDetail::create([
        'slip_id' => $header->id,
        'item_name' => '商品A',
        'quantity' => 1,
        'unit' => '個',
        'cost' => 1000,
        'tax_id' => 1,
        'price' => 1100,
    ]);

    $this->get('/orders/view/' . $header->id)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Orders/Show')
            ->where('order.orderNo', $header->order_no)
            ->where('order.customerName', $customer->name)
            ->has('order.lines', 1)
            ->where('order.lines.0.itemName', '商品A'));
});

// --- ステータス変更 ---------------------------------------------------

test('発行ステータスは押すたびに未発行と発行済みを往復する', function () {
    // 移行前は JSON を返していたが、Inertia では元のページへ戻す (back())。
    // フロントはリダイレクト先の props で新しい状態を受け取る。
    $header = createHeader(['is_issued' => 0]);

    $this->from('/orders')->post('/orders/set-status', ['id' => $header->id, 'type' => 'issued'])
        ->assertRedirect('/orders');

    expect((int) OrderHeader::find($header->id)->is_issued)->toBe(1);

    $this->from('/orders')->post('/orders/set-status', ['id' => $header->id, 'type' => 'issued']);

    expect((int) OrderHeader::find($header->id)->is_issued)->toBe(0);
});

test('受注ステータスは未受注→受注済み→失注→未受注と巡回する', function () {
    $header = createHeader(['is_ordered' => 0]);

    foreach ([1, 2, 0] as $expected) {
        $this->from('/orders')->post('/orders/set-status', ['id' => $header->id, 'type' => 'ordered']);

        expect((int) OrderHeader::find($header->id)->is_ordered)->toBe($expected);
    }
});

test('ステータス変更はクライアントの申告した現在値に依存しない', function () {
    // 移行前は currentStatus をクライアントから受け取って次の値を決めていたため、
    // 画面が古いと保存結果がずれた。
    $header = createHeader(['is_issued' => 1]);

    $this->from('/orders')->post('/orders/set-status', [
        'id' => $header->id,
        'type' => 'issued',
        'currentStatus' => 0, // 嘘の申告
    ]);

    expect((int) OrderHeader::find($header->id)->is_issued)->toBe(0);
});

test('未知のステータス種別は弾かれる', function () {
    $header = createHeader();

    $this->postJson('/orders/set-status', ['id' => $header->id, 'type' => 'unknown'])
        ->assertStatus(422);
});
