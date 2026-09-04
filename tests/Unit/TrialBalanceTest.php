<?php

use App\Domain\Accounting\Entity\Account;
use App\Domain\Accounting\Entity\JournalEntry;
use App\Domain\Accounting\Service\TrialBalanceBuilder;
use App\Domain\Accounting\ValueObject\AccountType;
use App\Domain\Accounting\ValueObject\BalanceSide;
use App\Domain\Shared\ValueObject\Money;

/**
 * 残高試算表の集計。
 *
 * 参考にした移植元 (CakePHP 時代の仕訳帳) は科目が自由入力の文字列で、
 * 集計そのものが存在しなかった。
 */
function account(int $id, string $name, AccountType $type, ?string $code = null): Account
{
    return Account::reconstitute($id, $name, $code, $type, true, null);
}

function entry(int $debitId, int $creditId, int $amount, string $date = '2026-09-04'): JournalEntry
{
    return JournalEntry::create(
        new DateTimeImmutable($date),
        $debitId,
        $creditId,
        Money::fromInt($amount),
        '摘要',
        null,
    );
}

test('借方と貸方が科目ごとに集計される', function () {
    $accounts = [
        account(1, '現金', AccountType::Asset),
        account(2, '売上', AccountType::Revenue),
    ];
    // 現金 1000 / 売上 1000 を 2 件
    $entries = [entry(1, 2, 1000), entry(1, 2, 500)];

    $rows = (new TrialBalanceBuilder())->build($accounts, $entries);

    expect($rows)->toHaveCount(2)
        ->and($rows[0]->account->name())->toBe('現金')
        ->and($rows[0]->debitTotal->amount)->toBe(1500)
        ->and($rows[0]->creditTotal->amount)->toBe(0)
        ->and($rows[1]->account->name())->toBe('売上')
        ->and($rows[1]->creditTotal->amount)->toBe(1500);
});

test('資産は借方に残高が立つ', function () {
    $accounts = [account(1, '現金', AccountType::Asset), account(2, '売上', AccountType::Revenue)];

    $rows = (new TrialBalanceBuilder())->build($accounts, [entry(1, 2, 1000)]);

    expect($rows[0]->side)->toBe(BalanceSide::Debit)
        ->and($rows[0]->balance->amount)->toBe(1000)
        ->and($rows[0]->debitBalance()->amount)->toBe(1000)
        ->and($rows[0]->creditBalance()->amount)->toBe(0);
});

test('収益は貸方に残高が立つ', function () {
    $accounts = [account(1, '現金', AccountType::Asset), account(2, '売上', AccountType::Revenue)];

    $rows = (new TrialBalanceBuilder())->build($accounts, [entry(1, 2, 1000)]);

    expect($rows[1]->side)->toBe(BalanceSide::Credit)
        ->and($rows[1]->creditBalance()->amount)->toBe(1000)
        ->and($rows[1]->debitBalance()->amount)->toBe(0);
});

test('本来と逆の側に残高が立つこともある', function () {
    // 現金 (資産) から出ていくほうが多ければ貸方残高になる。
    // 実務では入力誤りの兆候だが、集計としては正しく出す必要がある
    $accounts = [account(1, '現金', AccountType::Asset), account(2, '売上', AccountType::Revenue)];
    $entries = [entry(1, 2, 300), entry(2, 1, 1000)];

    $rows = (new TrialBalanceBuilder())->build($accounts, $entries);

    expect($rows[0]->account->name())->toBe('現金')
        ->and($rows[0]->side)->toBe(BalanceSide::Credit)
        ->and($rows[0]->balance->amount)->toBe(700);
});

test('借方合計と貸方合計は必ず一致する', function () {
    // 単一仕訳しか作れないので、試算表の縦計は常に貸借一致する。
    // 参考にした移植元は借方金額と貸方金額を別々に入力させており、
    // ずれた帳簿を作れてしまっていた
    $accounts = [
        account(1, '現金', AccountType::Asset),
        account(2, '売上', AccountType::Revenue),
        account(3, '仕入', AccountType::Expense),
    ];
    $entries = [entry(1, 2, 1000), entry(3, 1, 400), entry(3, 1, 250)];

    $rows = (new TrialBalanceBuilder())->build($accounts, $entries);

    $debit = array_sum(array_map(fn ($r) => $r->debitTotal->amount, $rows));
    $credit = array_sum(array_map(fn ($r) => $r->creditTotal->amount, $rows));

    expect($debit)->toBe($credit)->toBe(1650);
});

test('動きの無い科目は既定で行に出さない', function () {
    $accounts = [
        account(1, '現金', AccountType::Asset),
        account(2, '売上', AccountType::Revenue),
        account(3, '使っていない科目', AccountType::Expense),
    ];

    $rows = (new TrialBalanceBuilder())->build($accounts, [entry(1, 2, 100)]);

    expect($rows)->toHaveCount(2);
});

test('動きの無い科目も出せる', function () {
    $accounts = [
        account(1, '現金', AccountType::Asset),
        account(2, '売上', AccountType::Revenue),
        account(3, '使っていない科目', AccountType::Expense),
    ];

    $rows = (new TrialBalanceBuilder())->build($accounts, [entry(1, 2, 100)], includeEmpty: true);

    expect($rows)->toHaveCount(3)
        ->and($rows[2]->isEmpty())->toBeTrue();
});

test('区分の順に並ぶ', function () {
    // 資産 -> 負債 -> 純資産 -> 収益 -> 費用 (貸借対照表・損益計算書の慣習)
    $accounts = [
        account(5, '消耗品費', AccountType::Expense),
        account(4, '売上', AccountType::Revenue),
        account(3, '資本金', AccountType::Equity),
        account(2, '買掛金', AccountType::Liability),
        account(1, '現金', AccountType::Asset),
    ];
    $entries = [entry(1, 2, 100), entry(3, 4, 100), entry(5, 1, 100)];

    $rows = (new TrialBalanceBuilder())->build($accounts, $entries, includeEmpty: true);

    expect(array_map(fn ($r) => $r->account->name(), $rows))
        ->toBe(['現金', '買掛金', '資本金', '売上', '消耗品費']);
});

test('同じ区分ではコード順に並ぶ', function () {
    $accounts = [
        account(1, '普通預金', AccountType::Asset, '102'),
        account(2, '現金', AccountType::Asset, '101'),
    ];

    $rows = (new TrialBalanceBuilder())->build($accounts, [entry(2, 1, 100)]);

    expect(array_map(fn ($r) => $r->account->name(), $rows))->toBe(['現金', '普通預金']);
});
