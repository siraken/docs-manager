<?php

use App\Infrastructure\Persistence\Eloquent\Models\Account;
use Inertia\Testing\AssertableInertia;

/**
 * 勘定科目マスタと残高試算表。
 */

beforeEach(function () {
    actingAsUser(createUser());
});

// --- 勘定科目マスタ -------------------------------------------------

test('勘定科目の一覧が表示できる', function () {
    createAccount('現金', 'asset', ['code' => '101']);

    $this->get('/accounts')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Accounts/Index')
        ->has('accounts', 1)
        ->where('accounts.0.name', '現金')
        ->where('accounts.0.type', '資産')
        ->where('accounts.0.normalBalance', '借方')
        ->where('accounts.0.displayName', '101 現金'));
});

test('勘定科目を登録できる', function () {
    $this->post('/accounts/create', [
        'code' => '101',
        'name' => '現金',
        'type' => 'asset',
        'is_active' => true,
    ])->assertRedirect('/accounts');

    $account = Account::first();
    expect($account->name)->toBe('現金')
        ->and($account->type)->toBe('asset')
        ->and((bool) $account->is_active)->toBeTrue();
});

test('科目名が空だと登録できない', function () {
    $this->post('/accounts/create', ['name' => '', 'type' => 'asset'])
        ->assertSessionHasErrors('name');

    expect(Account::count())->toBe(0);
});

test('区分が不正だと登録できない', function () {
    $this->post('/accounts/create', ['name' => '現金', 'type' => 'unknown'])
        ->assertSessionHasErrors('type');

    expect(Account::count())->toBe(0);
});

test('科目コードは重複できない', function () {
    createAccount('現金', 'asset', ['code' => '101']);

    $this->post('/accounts/create', ['name' => '別の科目', 'type' => 'asset', 'code' => '101'])
        ->assertSessionHasErrors('code');

    expect(Account::count())->toBe(1);
});

test('自分自身のコードは重複扱いにならない', function () {
    $account = createAccount('現金', 'asset', ['code' => '101']);

    $this->post('/accounts/edit/' . $account->id, [
        'name' => '現金（改名）',
        'type' => 'asset',
        'code' => '101',
        'is_active' => true,
    ])->assertRedirect('/accounts');

    expect(Account::find($account->id)->name)->toBe('現金（改名）');
});

test('コードは省略できる', function () {
    $this->post('/accounts/create', ['name' => 'コード無し', 'type' => 'expense'])
        ->assertRedirect('/accounts');

    $account = Account::first();
    expect($account->code)->toBeNull();

    $this->get('/accounts')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('accounts.0.displayName', 'コード無し'));
});

test('科目を無効にすると仕訳フォームの選択肢から消える', function () {
    $account = createAccount('使わなくなった科目', 'expense');

    $this->post('/accounts/edit/' . $account->id, [
        'name' => '使わなくなった科目',
        'type' => 'expense',
        // is_active はチェックボックスなので、未チェックだと送信されない
    ])->assertRedirect('/accounts');

    expect((bool) Account::find($account->id)->is_active)->toBeFalse();

    $this->get('/journal/create')->assertInertia(fn (AssertableInertia $page) => $page->has('accounts', 0));
});

test('使われていない科目は削除できる', function () {
    $account = createAccount();

    $this->delete('/accounts/delete/' . $account->id)->assertRedirect('/accounts');

    expect(Account::count())->toBe(0);
});

test('仕訳で使われている科目は削除できない', function () {
    // 消すと過去の仕訳が宙に浮く
    [$debit, $credit] = createAccountPair();
    createJournalEntry($debit->id, $credit->id);

    $this->delete('/accounts/delete/' . $debit->id);

    expect(Account::find($debit->id))->not->toBeNull();
});

test('削除できない理由がメッセージで返る', function () {
    [$debit, $credit] = createAccountPair();
    createJournalEntry($debit->id, $credit->id);

    $this->delete('/accounts/delete/' . $credit->id);

    // DomainException は bootstrap/app.php が拾ってフラッシュに載せる
    $this->get('/accounts')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('flash.status', 'danger')
        ->where('flash.message', fn (string $m) => str_contains($m, '削除できません')));
});

test('存在しない科目は404になる', function () {
    $this->get('/accounts/edit/999')->assertNotFound();
});

// --- 残高試算表 -----------------------------------------------------

test('残高試算表が表示できる', function () {
    [$cash, $sales] = createAccountPair();
    createJournalEntry($cash->id, $sales->id, ['amount' => 1000]);

    $this->get('/journal/trial-balance')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Journal/TrialBalance')
        ->has('rows', 2)
        // 資産は借方、収益は貸方に残高が立つ
        ->where('rows.0.accountName', '現金')
        ->where('rows.0.debitBalanceLabel', '1,000')
        ->where('rows.0.creditBalanceLabel', '-')
        ->where('rows.1.accountName', '売上')
        ->where('rows.1.creditBalanceLabel', '1,000'));
});

test('試算表の貸借は一致する', function () {
    [$cash, $sales] = createAccountPair();
    $expense = createAccount('仕入', 'expense');

    createJournalEntry($cash->id, $sales->id, ['amount' => 1000]);
    createJournalEntry($expense->id, $cash->id, ['amount' => 400]);

    $this->get('/journal/trial-balance')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('totals.debitTotal', '1,400')
        ->where('totals.creditTotal', '1,400')
        ->where('totals.isBalanced', true));
});

test('試算表は年月で絞り込める', function () {
    [$cash, $sales] = createAccountPair();
    createJournalEntry($cash->id, $sales->id, ['date' => '2026-09-04', 'amount' => 1000]);
    createJournalEntry($cash->id, $sales->id, ['date' => '2026-10-01', 'amount' => 9999]);

    $this->get('/journal/trial-balance?year=2026&month=9')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('totals.debitTotal', '1,000')
            ->where('filter.year', 2026)
            ->where('filter.month', 9));
});

test('動きの無い科目は試算表に出ない', function () {
    [$cash, $sales] = createAccountPair();
    createAccount('使っていない科目', 'expense');
    createJournalEntry($cash->id, $sales->id);

    $this->get('/journal/trial-balance')->assertInertia(fn (AssertableInertia $page) => $page
        ->has('rows', 2));
});

test('試算表は科目の区分順に並ぶ', function () {
    // 資産 -> 負債 -> 純資産 -> 収益 -> 費用
    $cash = createAccount('現金', 'asset');
    $payable = createAccount('買掛金', 'liability');
    $sales = createAccount('売上', 'revenue');
    $expense = createAccount('仕入', 'expense');

    createJournalEntry($cash->id, $sales->id);
    createJournalEntry($expense->id, $payable->id);

    $this->get('/journal/trial-balance')->assertInertia(function (AssertableInertia $page): void {
        $names = array_column($page->toArray()['props']['rows'], 'accountName');
        expect($names)->toBe(['現金', '買掛金', '売上', '仕入']);
    });
});

test('仕訳が無くても試算表は開ける', function () {
    $this->get('/journal/trial-balance')->assertInertia(fn (AssertableInertia $page) => $page
        ->has('rows', 0)
        ->where('totals.isBalanced', true));
});

test('未認証では勘定科目にアクセスできない', function () {
    session()->flush();

    $this->get('/accounts')->assertRedirect('/login');
});
