<?php

use App\Infrastructure\Persistence\Eloquent\Models\Account;
use App\Infrastructure\Persistence\Eloquent\Models\JournalEntry;
use Inertia\Testing\AssertableInertia;

/**
 * 仕訳帳。
 *
 * in-house-timecard-app に CakePHP 時代のテンプレートだけが残っていた機能を、
 * 参考にしつつ新規に作り直したもの。参考実装で壊れていた箇所を中心に見る。
 */

beforeEach(function () {
    actingAsUser(createUser());
});

// --- 一覧と絞り込み -------------------------------------------------

test('一覧が表示できる', function () {
    [$debit, $credit] = createAccountPair();
    createJournalEntry($debit->id, $credit->id, ['description' => '一覧に出る取引']);

    $this->get('/journal')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Journal/Index')
        ->has('entries', 1)
        ->where('entries.0.description', '一覧に出る取引')
        ->where('entries.0.debitAccountName', '現金')
        ->where('entries.0.creditAccountName', '売上')
        ->where('entries.0.amountLabel', '1,000'));
});

test('年月で絞り込める', function () {
    [$debit, $credit] = createAccountPair();
    createJournalEntry($debit->id, $credit->id, ['date' => '2026-09-04', 'description' => '9月の取引']);
    createJournalEntry($debit->id, $credit->id, ['date' => '2026-10-01', 'description' => '10月の取引']);

    $this->get('/journal?year=2026&month=9')->assertInertia(fn (AssertableInertia $page) => $page
        ->has('entries', 1)
        ->where('entries.0.description', '9月の取引'));
});

test('摘要とその他の両方が検索される', function () {
    // 参考実装の検索欄も「摘要/その他で検索...」という範囲だった
    [$debit, $credit] = createAccountPair();
    createJournalEntry($debit->id, $credit->id, ['description' => '摘要に入っている']);
    createJournalEntry($debit->id, $credit->id, ['description' => 'ほか', 'note' => 'その他に入っている']);
    createJournalEntry($debit->id, $credit->id, ['description' => '無関係']);

    $this->get('/journal?q=' . urlencode('入っている'))->assertInertia(fn (AssertableInertia $page) => $page
        ->has('entries', 2));
});

test('勘定科目で絞り込める', function () {
    // 参考実装は日付でも科目でも絞れなかった
    [$cash, $sales] = createAccountPair();
    $expense = createAccount('消耗品費', 'expense');

    createJournalEntry($cash->id, $sales->id, ['description' => '現金が絡む']);
    createJournalEntry($expense->id, $sales->id, ['description' => '現金が絡まない']);

    $this->get('/journal?account_id=' . $cash->id)->assertInertia(fn (AssertableInertia $page) => $page
        ->has('entries', 1)
        ->where('entries.0.description', '現金が絡む'));
});

test('借方でも貸方でも同じ科目として拾われる', function () {
    [$cash, $sales] = createAccountPair();
    $expense = createAccount('消耗品費', 'expense');

    createJournalEntry($cash->id, $sales->id);      // 現金が借方
    createJournalEntry($expense->id, $cash->id);    // 現金が貸方

    $this->get('/journal?account_id=' . $cash->id)->assertInertia(fn (AssertableInertia $page) => $page
        ->has('entries', 2));
});

test('LIKE のワイルドカードは文字として扱う', function () {
    [$debit, $credit] = createAccountPair();
    createJournalEntry($debit->id, $credit->id);

    $this->get('/journal?q=%')->assertInertia(fn (AssertableInertia $page) => $page->has('entries', 0));
});

test('合計は絞り込み結果の全件を対象にする', function () {
    [$debit, $credit] = createAccountPair();
    createJournalEntry($debit->id, $credit->id, ['date' => '2026-09-01', 'amount' => 1000]);
    createJournalEntry($debit->id, $credit->id, ['date' => '2026-09-02', 'amount' => 500]);
    createJournalEntry($debit->id, $credit->id, ['date' => '2026-10-01', 'amount' => 9999]);

    $this->get('/journal?year=2026&month=9')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('total', 1500)
        ->where('totalLabel', '1,500'));
});

// --- 登録 -----------------------------------------------------------

test('仕訳を登録できる', function () {
    [$debit, $credit] = createAccountPair();

    $this->post('/journal/create', journalPayload($debit->id, $credit->id))
        ->assertRedirect('/journal');

    $entry = JournalEntry::first();
    expect($entry)->not->toBeNull()
        ->and((int) $entry->debit_account_id)->toBe($debit->id)
        ->and((int) $entry->credit_account_id)->toBe($credit->id)
        ->and((int) $entry->amount)->toBe(1000);
});

test('借方と貸方に同じ科目は指定できない', function () {
    // 「現金 / 現金」は何も動かない。参考実装には検証が無かった
    $account = createAccount();

    $this->post('/journal/create', journalPayload($account->id, $account->id))
        ->assertSessionHasErrors('credit_account_id');

    expect(JournalEntry::count())->toBe(0);
});

test('金額が0円だと登録できない', function () {
    [$debit, $credit] = createAccountPair();

    $this->post('/journal/create', journalPayload($debit->id, $credit->id, ['amount' => 0]))
        ->assertSessionHasErrors('amount');

    expect(JournalEntry::count())->toBe(0);
});

test('金額が負だと登録できない', function () {
    [$debit, $credit] = createAccountPair();

    $this->post('/journal/create', journalPayload($debit->id, $credit->id, ['amount' => -100]))
        ->assertSessionHasErrors('amount');

    expect(JournalEntry::count())->toBe(0);
});

test('摘要が空だと登録できない', function () {
    [$debit, $credit] = createAccountPair();

    $this->post('/journal/create', journalPayload($debit->id, $credit->id, ['description' => '']))
        ->assertSessionHasErrors('description');

    expect(JournalEntry::count())->toBe(0);
});

test('存在しない勘定科目は指定できない', function () {
    $account = createAccount();

    $this->post('/journal/create', journalPayload($account->id, 999))
        ->assertSessionHasErrors('credit_account_id');

    expect(JournalEntry::count())->toBe(0);
});

// --- フォーム -------------------------------------------------------

test('新規フォームの日付の既定値はサーバーが決める', function () {
    $this->get('/journal/create')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Journal/Form')
        ->where('entry', null)
        ->where('defaults.date', date('Y-m-d')));
});

test('新規フォームには無効化した科目が出ない', function () {
    createAccount('使う科目', 'asset');
    createAccount('使わない科目', 'expense', ['is_active' => false]);

    $this->get('/journal/create')->assertInertia(fn (AssertableInertia $page) => $page
        ->has('accounts', 1)
        ->where('accounts.0.name', '使う科目'));
});

test('編集フォームには無効化した科目も残る', function () {
    // 既にその科目で記帳された仕訳を開いたとき、選択が外れて
    // 別の科目に化けるのを防ぐ
    [$debit, $credit] = createAccountPair();
    $credit->update(['is_active' => false]);
    $entry = createJournalEntry($debit->id, $credit->id);

    $this->get('/journal/edit/' . $entry->id)->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Journal/Form')
        ->has('accounts', 2)
        ->where('entry.creditAccountId', $credit->id));
});

test('仕訳を更新できる', function () {
    [$debit, $credit] = createAccountPair();
    $entry = createJournalEntry($debit->id, $credit->id, ['description' => '変更前']);

    $this->post('/journal/edit/' . $entry->id, journalPayload($debit->id, $credit->id, [
        'description' => '変更後',
        'amount' => 2500,
    ]))->assertRedirect('/journal');

    $updated = JournalEntry::find($entry->id);
    expect($updated->description)->toBe('変更後')
        ->and((int) $updated->amount)->toBe(2500)
        ->and(JournalEntry::count())->toBe(1);
});

// --- 削除 -----------------------------------------------------------

test('仕訳を削除できる', function () {
    [$debit, $credit] = createAccountPair();
    $entry = createJournalEntry($debit->id, $credit->id);

    $this->delete('/journal/delete/' . $entry->id)->assertRedirect('/journal');

    expect(JournalEntry::count())->toBe(0);
});

test('存在しない仕訳は404になる', function () {
    $this->get('/journal/edit/999')->assertNotFound();
});

test('削除された科目を参照していても一覧は開ける', function () {
    // 通常は削除できないが、直接 DB から消された場合に一覧ごと
    // 落ちないようにしてある
    [$debit, $credit] = createAccountPair();
    createJournalEntry($debit->id, $credit->id);
    Account::where('id', $credit->id)->delete();

    $this->get('/journal')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('entries.0.creditAccountName', '(削除された科目)'));
});

test('未認証では仕訳帳にアクセスできない', function () {
    session()->flush();

    $this->get('/journal')->assertRedirect('/login');
});
