<?php

use App\Infrastructure\Persistence\Eloquent\Models\Travel;
use App\Infrastructure\Persistence\Eloquent\Models\TravelExpense;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia;

/**
 * CSV 取り込みのリグレッションテスト。
 *
 * SplFileObject の READ_CSV に依存しており、列は 0 始まりではなく
 * $row[1] から読まれる (先頭列は使われない) という癖がある。
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

/** 旅費精算: 16 列必要 ($row[1]..$row[15])。末尾の合計は読まれない */
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

test('見出し行をデータとして読ませようとすると取り込み全体が失敗する', function () {
    // 移行前は見出し行がそのまま 1 レコードとして保存され、日付カラムに
    // "date_from" のような文字列が入っていた。日付として解釈できない行が
    // あれば、その取り込みは丸ごと取り消す。
    $csv = "no,rel_id,dir,purpose,price,date_from,date_to,apply_date,apply_person\n" . travelRow();

    $response = $this->post('/trips/import', ['csv' => csvFile($csv)]);

    $response->assertRedirect('/trips');
    $response->assertSessionHas('flash_status', 'danger');
    expect(Travel::count())->toBe(0);
});

test('壊れた行があると1件も取り込まれない', function () {
    $broken = implode(',', ['IGNORED', '1', '大阪', '打ち合わせ', '1000', 'not-a-date', '2026-09-11', '2026-09-01', '花子']) . "\n";

    $this->post('/trips/import', ['csv' => csvFile(travelRow() . $broken)]);

    // 正しい 1 行目もロールバックされる
    expect(Travel::count())->toBe(0);
});

test('CSVファイルが無いとエラーになる', function () {
    $this->post('/trips/import', [])->assertSessionHasErrors('csv');
});

test('出張旅費精算のCSVを取り込める', function () {
    $response = $this->post('/expenses/import', ['csv' => csvFile(expenseRow())]);

    // 移行前は精算の取り込みでも /trips に戻っていた
    $response->assertRedirect('/expenses');
    expect(TravelExpense::count())->toBe(1);

    $expense = TravelExpense::first();
    expect($expense->dir)->toBe('東京')
        ->and($expense->apply_person)->toBe('テスト太郎')
        ->and((int) $expense->trans_fee)->toBe(1000)
        // 合計は CSV の末尾ではなく内訳の和 (1000+2000+300+1500+800+3000)
        ->and((int) $expense->total_fee)->toBe(8600);
});

test('旅費精算の合計はCSVの申告ではなく内訳から計算される', function () {
    $row = implode(',', [
        'IGNORED', '1', '東京', '打ち合わせ', '2026-09-01', '2026-09-10', '2026-09-11',
        '2026-09-20', 'テスト太郎', '1000', '2000', '300', '1500', '800', '3000',
        '999999', // 食い違った合計
    ]) . "\n";

    $this->post('/expenses/import', ['csv' => csvFile($row)]);

    expect((int) TravelExpense::first()->total_fee)->toBe(8600);
});

test('取り込んだ出張申請が一覧の props に出る', function () {
    $this->post('/trips/import', ['csv' => csvFile(travelRow('那覇', 'テスト太郎'))]);

    $this->get('/trips')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Trips/Index')
        ->has('trips', 1)
        ->where('trips.0.destination', '那覇')
        ->where('trips.0.urls.pdf', url('/trips/pdf/' . Travel::first()->id)));
});

test('取り込んだ旅費精算が一覧の props に出る', function () {
    $this->post('/expenses/import', ['csv' => csvFile(expenseRow())]);

    $expense = TravelExpense::first();

    $this->get('/expenses')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Expenses/Index')
        ->has('expenses', 1)
        ->where('expenses.0.destination', '東京')
        // 合計は CSV 末尾の申告 (8600) ではなく費目の合計
        ->where('expenses.0.totalFee', (int) $expense->total_fee));
});
