<?php

use App\Domain\Shared\ValueObject\Money;
use App\Domain\Travel\Entity\TravelExpense;

/**
 * 出張旅費精算。合計は内訳から導出する。
 *
 * 移行前は total_fee をフォームや CSV から受け取った値のまま保存していたため、
 * 内訳と合計が食い違いうる状態だった。
 */

function expense(int $trans, int $acm, int $gas, int $dinner, int $lunch, int $daily): TravelExpense
{
    return TravelExpense::create(
        relId: '1',
        destination: '東京',
        purpose: '打ち合わせ',
        applyDate: new DateTimeImmutable('2026-09-01'),
        dateFrom: new DateTimeImmutable('2026-09-10'),
        dateTo: new DateTimeImmutable('2026-09-11'),
        payDate: new DateTimeImmutable('2026-09-20'),
        applyPerson: 'テスト太郎',
        transportationFee: Money::fromInt($trans),
        accommodationFee: Money::fromInt($acm),
        gasFee: Money::fromInt($gas),
        dinnerFee: Money::fromInt($dinner),
        lunchFee: Money::fromInt($lunch),
        dailyAllowance: Money::fromInt($daily),
    );
}

test('合計は費目の和になる', function () {
    expect(expense(1000, 2000, 300, 1500, 800, 3000)->totalFee()->amount)->toBe(8600);
});

test('費目が全て0なら合計も0', function () {
    expect(expense(0, 0, 0, 0, 0, 0)->totalFee()->amount)->toBe(0);
});

test('更新すると合計も付いてくる', function () {
    $expense = expense(1000, 0, 0, 0, 0, 0);

    $expense->update(
        relId: '1',
        destination: '大阪',
        purpose: '打ち合わせ',
        applyDate: new DateTimeImmutable('2026-09-01'),
        dateFrom: new DateTimeImmutable('2026-09-10'),
        dateTo: new DateTimeImmutable('2026-09-11'),
        payDate: new DateTimeImmutable('2026-09-20'),
        applyPerson: 'テスト太郎',
        transportationFee: Money::fromInt(2000),
        accommodationFee: Money::fromInt(1000),
        gasFee: Money::zero(),
        dinnerFee: Money::zero(),
        lunchFee: Money::zero(),
        dailyAllowance: Money::zero(),
    );

    expect($expense->destination())->toBe('大阪')
        ->and($expense->totalFee()->amount)->toBe(3000);
});
