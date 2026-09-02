<?php

use App\Models\OrderDetail;
use App\Models\OrderHeader;
use App\Models\Travel;

/**
 * PDF / CSV 出力のスモークテスト。
 *
 * これらのアクションは Laravel の Response を返さず、TCPDF の Output() や
 * readfile() で直接出力する。PHP や依存ライブラリのバージョンを上げた際に
 * 例外なく生成できることを担保するのが目的で、レイアウトの検証はしない。
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
        'price' => 1000,
    ]);

    return $header;
}

test('発注書のPDFが生成される', function () {
    $header = createOrderWithDetail();

    ob_start();
    try {
        $this->get('/orders/pdf/' . $header->id);
    } finally {
        $output = ob_get_clean();
    }

    expect($output)->toStartWith('%PDF-')
        ->and(strlen($output))->toBeGreaterThan(1000);
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

    ob_start();
    try {
        $this->get('/trips/pdf/' . $travel->id);
    } finally {
        $output = ob_get_clean();
    }

    expect($output)->toStartWith('%PDF-');
});

test('発注書のCSVが出力される', function () {
    $this->markTestIncomplete(
        'csv() は Laravel の Response を使わず素の header() を呼ぶため、'
        . 'テストランナーが既に出力している状態では "headers already sent" になり検証できない。'
        . '実 HTTP では動作する。Response 返却に書き換えればこのテストを有効化できる。'
    );

    $header = createOrderWithDetail('CSV-001');

    ob_start();
    try {
        $this->get('/orders/csv/' . $header->id);
    } finally {
        $output = ob_get_clean();
    }

    expect($output)->toContain('商品A')->toContain('slip_id');
});

test('明細のない発注書のCSV出力は失敗する', function () {
    $customer = createCustomer();
    $header = createHeader([
        'customer_id' => $customer->id,
        'order_no' => 'EMPTY-001',
        'subtotal_price' => 0,
        'tax_price' => 0,
        'total_price' => 0,
        'remarks' => null,
    ]);

    // csv() は $details[0] を無条件に参照するため、明細が無いと落ちる。
    // 現状の挙動を記録しておく。
    $this->withoutExceptionHandling();
    $this->expectException(ErrorException::class);

    ob_start();
    try {
        $this->get('/orders/csv/' . $header->id);
    } finally {
        ob_get_clean();
    }
});
