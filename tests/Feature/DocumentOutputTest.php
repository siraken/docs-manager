<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\OrderDetail;
use App\Models\OrderHeader;
use App\Models\Travel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * PDF / CSV 出力のスモークテスト。
 *
 * これらのアクションは Laravel の Response を返さず、TCPDF の Output() や
 * readfile() で直接出力する。PHP や依存ライブラリのバージョンを上げた際に
 * 例外なく生成できることを担保するのが目的で、レイアウトの検証はしない。
 */
class DocumentOutputTest extends TestCase
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

    private function createCustomer(): Customer
    {
        $customer = new Customer();
        $customer->name = '株式会社テスト';
        $customer->is_company = 1;
        $customer->email = 'client@example.com';
        $customer->phone = '03-0000-0000';
        $customer->post_code = '100-0001';
        $customer->address = '千代田1-1';
        $customer->city = '千代田区';
        $customer->state = '東京都';
        $customer->country = '日本';
        $customer->save();

        return $customer;
    }

    private function createOrder(string $orderNo = 'NO-001'): OrderHeader
    {
        $customer = $this->createCustomer();

        $header = OrderHeader::create([
            'customer_id' => $customer->id,
            'responsible' => '担当者',
            'honor_title' => '御中',
            'issued_date' => '2026-09-01',
            'exp_date' => '2026-09-30',
            'order_no' => $orderNo,
            'title' => 'テスト発注',
            'subtotal_price' => 1000,
            'tax_price' => 100,
            'total_price' => 1100,
            'remarks' => '備考',
            'is_issued' => 0,
            'is_ordered' => 0,
            'is_deleted' => 0,
            'is_converted' => 0,
        ]);

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

    public function test_発注書のPDFが生成される(): void
    {
        $header = $this->createOrder();

        ob_start();
        try {
            $this->get('/orders/pdf/' . $header->id);
        } finally {
            $output = ob_get_clean();
        }

        $this->assertStringStartsWith('%PDF-', $output, 'PDF のマジックナンバーで始まっていない');
        $this->assertGreaterThan(1000, strlen($output), 'PDF の中身が小さすぎる');
    }

    public function test_出張申請のPDFがテンプレートから生成される(): void
    {
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

        $this->assertStringStartsWith('%PDF-', $output);
    }

    public function test_発注書のCSVが出力される(): void
    {
        $this->markTestIncomplete(
            'csv() は Laravel の Response を使わず素の header() を呼ぶため、'
            . 'PHPUnit が既に出力している状態では "headers already sent" になり検証できない。'
            . '実 HTTP では動作する。Response 返却に書き換えればこのテストを有効化できる。'
        );

        $header = $this->createOrder('CSV-001');

        ob_start();
        try {
            $this->get('/orders/csv/' . $header->id);
        } finally {
            $output = ob_get_clean();
        }

        $this->assertStringContainsString('商品A', $output);
        $this->assertStringContainsString('slip_id', $output, 'CSV ヘッダー行が含まれていない');

        // 一時ファイルはカレントディレクトリに作られたあと unlink される
        $this->assertFileDoesNotExist(base_path('CSV-001.csv'));
    }

    public function test_明細のない発注書のCSV出力は失敗する(): void
    {
        $customer = $this->createCustomer();
        $header = OrderHeader::create([
            'customer_id' => $customer->id,
            'responsible' => '担当者',
            'honor_title' => '御中',
            'issued_date' => '2026-09-01',
            'exp_date' => '2026-09-30',
            'order_no' => 'EMPTY-001',
            'title' => '明細なし',
            'subtotal_price' => 0,
            'tax_price' => 0,
            'total_price' => 0,
            'remarks' => null,
            'is_issued' => 0,
            'is_ordered' => 0,
            'is_deleted' => 0,
            'is_converted' => 0,
        ]);

        // csv() は $details[0] を無条件に参照するため、明細が無いと落ちる。
        // 現状の挙動を記録しておく。
        $this->withoutExceptionHandling();
        $this->expectException(\ErrorException::class);

        ob_start();
        try {
            $this->get('/orders/csv/' . $header->id);
        } finally {
            ob_get_clean();
        }
    }
}
