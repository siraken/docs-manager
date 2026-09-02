<?php

use App\Models\Travel;
use App\Models\TravelExpense;
use Illuminate\Http\UploadedFile;

/**
 * CSV 取り込み (TravelController / TravelExpenseController) のリグレッションテスト。
 *
 * SplFileObject の READ_CSV に依存しており、列は 0 始まりではなく
 * $row[1] から読まれる (先頭列は使われない) という癖がある。
 * PHP のバージョンを上げた際にこの読み取り結果が変わらないことを検出する。
 */

beforeEach(function () {
    actingAsUser(createUser());
});

function csvFile(string $contents): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'csv') . '.csv';
    file_put_contents($path, $contents);

    return new UploadedFile($path, 'import.csv', 'text/csv', null, true);
}

/** 出張申請: 先頭列は読まれないため 9 列必要 ($row[1]..$row[8]) */
function travelRow(string $dir = '東京', string $person = 'テスト太郎'): string
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
function expenseRow(): string
{
    return implode(',', [
        'IGNORED', '1', '東京', '打ち合わせ', '2026-09-01', '2026-09-10', '2026-09-11',
        '2026-09-20', 'テスト太郎', '1000', '2000', '300', '1500', '800', '3000', '8600',
    ]) . "\n";
}

test('出張申請のCSVを取り込める', function () {
    $csv = travelRow('東京', 'テスト太郎') . travelRow('大阪', 'テスト花子');

    $response = $this->post('/trips/import', ['csv' => csvFile($csv)]);

    $response->assertRedirect('/trips');
    $response->assertSessionHas('flash_status', 'success');
    expect(Travel::count())->toBe(2);

    $first = Travel::orderBy('id')->first();
    expect($first->dir)->toBe('東京')
        ->and($first->purpose)->toBe('打ち合わせ')
        ->and((int) $first->price)->toBe(12000)
        ->and($first->apply_person)->toBe('テスト太郎');
});

test('先頭列は読み飛ばされる', function () {
    $this->post('/trips/import', ['csv' => csvFile(travelRow())]);

    // [0] の 'IGNORED' はどのカラムにも入らない
    $travel = Travel::first();
    expect((string) $travel->rel_id)->toBe('1')
        ->and($travel->dir)->not->toBe('IGNORED');
});

test('headerパラメータが真なら1行目を読み飛ばす', function () {
    $csv = "no,rel_id,dir,purpose,price,date_from,date_to,apply_date,apply_person\n" . travelRow();

    $this->post('/trips/import', ['csv' => csvFile($csv), 'header' => '1']);

    expect(Travel::count())->toBe(1)
        ->and(Travel::first()->dir)->toBe('東京');
});

test('headerを指定しなければ1行目もデータとして取り込まれる', function () {
    $csv = "no,rel_id,dir,purpose,price,date_from,date_to,apply_date,apply_person\n" . travelRow();

    $this->post('/trips/import', ['csv' => csvFile($csv)]);

    // 見出し行がそのまま 1 レコードとして入る
    expect(Travel::count())->toBe(2)
        ->and(Travel::orderBy('id')->first()->dir)->toBe('dir');
});

test('出張旅費精算のCSVを取り込める', function () {
    $response = $this->post('/expenses/import', ['csv' => csvFile(expenseRow())]);

    // 精算側もリダイレクト先は /trips (現状の挙動)
    $response->assertRedirect('/trips');
    expect(TravelExpense::count())->toBe(1);

    $expense = TravelExpense::first();
    expect($expense->dir)->toBe('東京')
        ->and($expense->apply_person)->toBe('テスト太郎')
        ->and((int) $expense->trans_fee)->toBe(1000)
        ->and((int) $expense->total_fee)->toBe(8600);
});
