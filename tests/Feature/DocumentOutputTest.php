<?php

use App\Infrastructure\Persistence\Eloquent\Models\OrderDetail;
use App\Infrastructure\Persistence\Eloquent\Models\OrderHeader;
use App\Infrastructure\Persistence\Eloquent\Models\Setting;
use App\Infrastructure\Persistence\Eloquent\Models\Travel;

/**
 * PDF / CSV 出力のテスト。
 *
 * 移行前はこれらのアクションが Laravel の Response を返さず、TCPDF の Output() や
 * readfile() で直接出力していたため、出力バッファを掴まないと検証できず、
 * CSV に至っては "headers already sent" で検証自体が不可能だった。
 * いまはどちらも通常のレスポンスとして返るので、素直に本文を見られる。
 */

beforeEach(function () {
    actingAsUser(createUser());
});

/** 明細付きの発注書を 1 件作る */
function createOrderWithDetail(string $orderNo = 'NO-001'): OrderHeader
{
    $customer = createCustomer();
    $header = createHeader(['customer_id' => $customer->id, 'order_no' => $orderNo]);

    OrderDetail::create([
        'slip_id' => $header->id,
        'item_name' => '商品A',
        'quantity' => 1,
        'unit' => '個',
        'cost' => 1000,
        'tax_id' => 1,
        'price' => 1100,
    ]);

    return $header;
}

test('発注書のPDFが生成される', function () {
    $header = createOrderWithDetail();

    $response = $this->get('/orders/pdf/' . $header->id);

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    expect($response->getContent())->toStartWith('%PDF-')
        ->and(strlen($response->getContent()))->toBeGreaterThan(1000);
});

test('PDFのファイル名は発注書番号になる', function () {
    $header = createOrderWithDetail('NO-2026-001');

    $this->get('/orders/pdf/' . $header->id)
        ->assertHeader('Content-Disposition', 'inline; filename="NO-2026-001.pdf"');
});

test('顧客が削除済みでもPDFは生成できる', function () {
    // 移行前は宛先の顧客を無条件に参照していたため 500 になっていた
    $header = createHeader(['customer_id' => 999]);

    $this->get('/orders/pdf/' . $header->id)->assertOk();
});

test('PDFの差出人欄には保存済みの自社情報が使われる', function () {
    Setting::create([
        'name' => 'テスト商会',
        'zipcode' => '100-0001',
        'address' => "東京都千代田区1-1\n2階",
        'rep' => '代表 太郎',
        'tel_no' => '03-0000-0000',
        'logo_url' => '',
        'com_stamp_url' => '',
        'rep_stamp_url' => '',
        'apply_stamp_url' => '',
    ]);

    $header = createOrderWithDetail();

    // 内容の座標までは検証しない。設定を読んでも例外なく生成できることを見る
    $this->get('/orders/pdf/' . $header->id)->assertOk();
});

test('出張申請のPDFがテンプレートから生成される', function () {
    $travel = Travel::create([
        'rel_id' => 1,
        'dir' => '東京',
        'purpose' => '打ち合わせ',
        'price' => 12000,
        'date_from' => '2026-09-10',
        'date_to' => '2026-09-11',
        'apply_date' => '2026-09-01',
        'apply_person' => 'テスト太郎',
    ]);

    $response = $this->get('/trips/pdf/' . $travel->id);

    $response->assertOk();
    expect($response->getContent())->toStartWith('%PDF-');
});

test('発注書のCSVが出力される', function () {
    $header = createOrderWithDetail('CSV-001');

    $response = $this->get('/orders/csv/' . $header->id);

    $response->assertOk();
    $response->assertHeader('Content-Disposition', 'attachment; filename="CSV-001.csv"');

    $csv = $response->getContent();
    expect($csv)->toContain('order_no')
        ->toContain('item_name')
        ->toContain('商品A')
        ->toContain('CSV-001');
});

test('明細のない発注書もCSVを出力できる', function () {
    // 移行前は $details[0] を無条件に参照していたため ErrorException で落ちていた
    $customer = createCustomer();
    $header = createHeader([
        'customer_id' => $customer->id,
        'order_no' => 'EMPTY-001',
        'subtotal_price' => 0,
        'tax_price' => 0,
        'total_price' => 0,
        'remarks' => null,
    ]);

    $response = $this->get('/orders/csv/' . $header->id);

    $response->assertOk();
    $csv = $response->getContent();
    expect($csv)->toContain('EMPTY-001')
        // ヘッダー行 + データ 1 行
        ->and(substr_count(trim($csv), "\n"))->toBe(1);
});

test('CSV出力はカレントディレクトリにファイルを残さない', function () {
    // 移行前は './' . $order_no . '.csv' を作ってから readfile + unlink していた
    $header = createOrderWithDetail('SIDE-EFFECT');

    $before = scandir(base_path());
    $this->get('/orders/csv/' . $header->id)->assertOk();
    $after = scandir(base_path());

    expect(array_diff($after, $before))->toBe([]);
});

test('存在しない発注書のPDFは404になる', function () {
    $this->get('/orders/pdf/999')->assertNotFound();
});
