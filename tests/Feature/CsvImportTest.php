<?php

namespace Tests\Feature;

use App\Models\Travel;
use App\Models\TravelExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * CSV 取り込み (TravelController / TravelExpenseController) のリグレッションテスト。
 *
 * SplFileObject の READ_CSV に依存しており、列は 0 始まりではなく
 * $row[1] から読まれる (先頭列は使われない) という癖がある。
 * PHP のバージョンを上げた際にこの読み取り結果が変わらないことを検出する。
 */
class CsvImportTest extends TestCase
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

    private function csvFile(string $contents): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'csv') . '.csv';
        file_put_contents($path, $contents);

        return new UploadedFile($path, 'import.csv', 'text/csv', null, true);
    }

    /** 出張申請: 先頭列は読まれないため 9 列必要 ($row[1]..$row[8]) */
    private function travelRow(string $dir = '東京', string $person = 'テスト太郎'): string
    {
        return implode(',', [
            'IGNORED',      // [0] 使われない
            '1',            // [1] rel_id
            $dir,           // [2] dir
            '打ち合わせ',    // [3] purpose
            '12000',        // [4] price
            '2026-09-10',   // [5] date_from
            '2026-09-11',   // [6] date_to
            '2026-09-01',   // [7] apply_date
            $person,        // [8] apply_person
        ]) . "\n";
    }

    /** 旅費精算: 16 列必要 ($row[1]..$row[15]) */
    private function expenseRow(): string
    {
        return implode(',', [
            'IGNORED',      // [0] 使われない
            '1',            // [1] rel_id
            '東京',          // [2] dir
            '打ち合わせ',    // [3] purpose
            '2026-09-01',   // [4] apply_date
            '2026-09-10',   // [5] date_from
            '2026-09-11',   // [6] date_to
            '2026-09-20',   // [7] pay_date
            'テスト太郎',    // [8] apply_person
            '1000',         // [9]  trans_fee
            '2000',         // [10] acm_fee
            '300',          // [11] gas_fee
            '1500',         // [12] dinner_fee
            '800',          // [13] lunch_fee
            '3000',         // [14] daily_pay
            '8600',         // [15] total_fee
        ]) . "\n";
    }

    public function test_出張申請のCSVを取り込める(): void
    {
        $csv = $this->travelRow('東京', 'テスト太郎')
             . $this->travelRow('大阪', 'テスト花子');

        $response = $this->post('/trips/import', ['csv' => $this->csvFile($csv)]);

        $response->assertRedirect('/trips');
        $response->assertSessionHas('flash_status', 'success');
        $this->assertSame(2, Travel::count());

        $first = Travel::orderBy('id')->first();
        $this->assertSame('東京', $first->dir);
        $this->assertSame('打ち合わせ', $first->purpose);
        $this->assertSame(12000, (int) $first->price);
        $this->assertSame('テスト太郎', $first->apply_person);
    }

    public function test_先頭列は読み飛ばされる(): void
    {
        $this->post('/trips/import', ['csv' => $this->csvFile($this->travelRow())]);

        // [0] の 'IGNORED' はどのカラムにも入らない
        $travel = Travel::first();
        $this->assertSame('1', (string) $travel->rel_id);
        $this->assertNotSame('IGNORED', $travel->dir);
    }

    public function test_headerパラメータが真なら1行目を読み飛ばす(): void
    {
        $csv = "no,rel_id,dir,purpose,price,date_from,date_to,apply_date,apply_person\n"
             . $this->travelRow();

        $this->post('/trips/import', [
            'csv' => $this->csvFile($csv),
            'header' => '1',
        ]);

        $this->assertSame(1, Travel::count());
        $this->assertSame('東京', Travel::first()->dir);
    }

    public function test_headerを指定しなければ1行目もデータとして取り込まれる(): void
    {
        $csv = "no,rel_id,dir,purpose,price,date_from,date_to,apply_date,apply_person\n"
             . $this->travelRow();

        $this->post('/trips/import', ['csv' => $this->csvFile($csv)]);

        // 見出し行がそのまま 1 レコードとして入る
        $this->assertSame(2, Travel::count());
        $this->assertSame('dir', Travel::orderBy('id')->first()->dir);
    }

    public function test_出張旅費精算のCSVを取り込める(): void
    {
        $response = $this->post('/expenses/import', [
            'csv' => $this->csvFile($this->expenseRow()),
        ]);

        // 精算側もリダイレクト先は /trips (現状の挙動)
        $response->assertRedirect('/trips');
        $this->assertSame(1, TravelExpense::count());

        $expense = TravelExpense::first();
        $this->assertSame('東京', $expense->dir);
        $this->assertSame('テスト太郎', $expense->apply_person);
        $this->assertSame(1000, (int) $expense->trans_fee);
        $this->assertSame(8600, (int) $expense->total_fee);
    }
}
