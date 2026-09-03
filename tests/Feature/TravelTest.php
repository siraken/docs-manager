<?php

use App\Infrastructure\Persistence\Eloquent\Models\Travel;
use App\Infrastructure\Persistence\Eloquent\Models\TravelExpense;
use Inertia\Testing\AssertableInertia;

/**
 * 出張申請 / 旅費精算の画面。
 *
 * CSV の取り込みは CsvImportTest、PDF は DocumentOutputTest にある。
 * ここは画面に渡る props を見る。
 */

beforeEach(function () {
    actingAsUser(createUser());
});

function createTravel(array $attributes = []): Travel
{
    return Travel::create(array_merge([
        'rel_id' => '20260901-Num',
        'dir' => '福岡',
        'purpose' => "打ち合わせ\n現地調査",
        'price' => 12000,
        'date_from' => '2026-09-10',
        'date_to' => '2026-09-11',
        'apply_date' => '2026-09-01',
        'apply_person' => 'テスト太郎',
    ], $attributes));
}

function createExpense(array $attributes = []): TravelExpense
{
    return TravelExpense::create(array_merge([
        'rel_id' => '20260901-Num',
        'dir' => '東京',
        'purpose' => '打ち合わせ',
        'apply_date' => '2026-09-01',
        'date_from' => '2026-09-10',
        'date_to' => '2026-09-11',
        'pay_date' => '2026-09-20',
        'apply_person' => 'テスト太郎',
        'trans_fee' => 1000,
        'acm_fee' => 2000,
        'gas_fee' => 300,
        'lunch_fee' => 1500,
        'dinner_fee' => 800,
        'daily_pay' => 3000,
        'total_fee' => 8600,
    ], $attributes));
}

// --- 出張申請 -------------------------------------------------------

test('出張申請の詳細が props で渡る', function () {
    $travel = createTravel();

    $this->get('/trips/view/' . $travel->id)->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Trips/Show')
        ->where('trip.destination', '福岡')
        ->where('trip.priceLabel', '12,000')
        // 目的は改行を含む。一覧に出す短縮版も props に載せる
        ->where('trip.purpose', "打ち合わせ\n現地調査")
        ->where('trip.urls.pdf', url('/trips/pdf/' . $travel->id)));
});

test('出張申請フォームには既定値が渡る', function () {
    // 既定値はサーバー側で決める (画面が date() を持たない)
    $this->get('/trips/create')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Trips/Form')
        ->where('defaults.applyDate', date('Y-m-d'))
        ->where('defaults.dateFrom', date('Y-m-d', strtotime('+1 day')))
        ->where('defaults.dateTo', date('Y-m-d', strtotime('+1 week'))));
});

test('出張申請を登録できる', function () {
    $this->post('/trips/create', [
        'rel_id' => '20260901-1',
        'dir' => '札幌',
        'purpose' => '商談',
        'price' => 30000,
        'date_from' => '2026-10-01',
        'date_to' => '2026-10-03',
        'apply_date' => '2026-09-20',
        'apply_person' => 'テスト太郎',
    ])->assertRedirect('/trips');

    expect(Travel::where('dir', '札幌')->exists())->toBeTrue();
});

test('帰着日が出発日より前だと登録できない', function () {
    $this->post('/trips/create', [
        'rel_id' => '20260901-1',
        'dir' => '札幌',
        'purpose' => '商談',
        'date_from' => '2026-10-03',
        'date_to' => '2026-10-01',
        'apply_date' => '2026-09-20',
        'apply_person' => 'テスト太郎',
    ])->assertSessionHasErrors('date_to');

    expect(Travel::count())->toBe(0);
});

// --- 旅費精算 -------------------------------------------------------

test('旅費精算の詳細が props で渡る', function () {
    $expense = createExpense();

    $this->get('/expenses/view/' . $expense->id)->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Expenses/Show')
        ->where('expense.transportationFee', 1000)
        ->where('expense.dailyAllowance', 3000)
        ->where('expense.urls.edit', url('/expenses/edit/' . $expense->id)));
});

test('旅費精算の新規フォームには空の入れ物が渡る', function () {
    // id が無いので urls は組めない
    $this->get('/expenses/create')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Expenses/Form')
        ->where('isNew', true)
        ->where('expense.id', null)
        ->where('expense.urls', null)
        ->where('expense.totalFee', 0));
});

test('旅費精算の編集フォームには費目が渡る', function () {
    $expense = createExpense();

    $this->get('/expenses/edit/' . $expense->id)->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Expenses/Form')
        ->where('isNew', false)
        ->where('expense.transportationFee', 1000)
        ->where('expense.accommodationFee', 2000));
});

test('合計はフォームの申告ではなく費目から計算される', function () {
    // 移行前はフォームに費目の入力欄が無く、PDF だけがこれらを印字していた。
    $this->post('/expenses/create', [
        'rel_id' => '20260901-1',
        'dir' => '名古屋',
        'purpose' => '打ち合わせ',
        'apply_date' => '2026-09-01',
        'pay_date' => '2026-09-20',
        'date_from' => '2026-09-10',
        'date_to' => '2026-09-11',
        'apply_person' => 'テスト太郎',
        'trans_fee' => 1000,
        'acm_fee' => 2000,
        'gas_fee' => 300,
        'lunch_fee' => 1500,
        'dinner_fee' => 800,
        'daily_pay' => 3000,
        'total_fee' => 999999,   // 申告は無視される
    ])->assertRedirect('/expenses');

    expect((int) TravelExpense::first()->total_fee)->toBe(8600);
});
