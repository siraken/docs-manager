<?php

use App\Domain\Accounting\ValueObject\AccountType;
use App\Domain\Accounting\ValueObject\BalanceSide;

/**
 * 勘定科目の区分。残高がどちらの側に立つかを決める。
 */

test('資産と費用は借方に残高が立つ', function () {
    expect(AccountType::Asset->normalBalance())->toBe(BalanceSide::Debit)
        ->and(AccountType::Expense->normalBalance())->toBe(BalanceSide::Debit);
});

test('負債と純資産と収益は貸方に残高が立つ', function () {
    expect(AccountType::Liability->normalBalance())->toBe(BalanceSide::Credit)
        ->and(AccountType::Equity->normalBalance())->toBe(BalanceSide::Credit)
        ->and(AccountType::Revenue->normalBalance())->toBe(BalanceSide::Credit);
});

test('区分の表示名が定義されている', function () {
    expect(AccountType::Asset->label())->toBe('資産')
        ->and(AccountType::Liability->label())->toBe('負債')
        ->and(AccountType::Equity->label())->toBe('純資産')
        ->and(AccountType::Revenue->label())->toBe('収益')
        ->and(AccountType::Expense->label())->toBe('費用');
});

test('並び順は貸借対照表の慣習に沿う', function () {
    $sorted = AccountType::cases();
    usort($sorted, fn ($a, $b) => $a->sortOrder() <=> $b->sortOrder());

    expect(array_map(fn ($t) => $t->value, $sorted))
        ->toBe(['asset', 'liability', 'equity', 'revenue', 'expense']);
});

test('選択肢は5区分', function () {
    expect(AccountType::options())->toHaveCount(5);
});

test('貸借は反転できる', function () {
    expect(BalanceSide::Debit->opposite())->toBe(BalanceSide::Credit)
        ->and(BalanceSide::Credit->opposite())->toBe(BalanceSide::Debit)
        ->and(BalanceSide::Debit->label())->toBe('借方')
        ->and(BalanceSide::Credit->label())->toBe('貸方');
});
