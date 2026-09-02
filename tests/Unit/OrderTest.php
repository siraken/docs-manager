<?php

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\OrderLine;
use App\Domain\Order\ValueObject\IssueStatus;
use App\Domain\Order\ValueObject\OrderNo;
use App\Domain\Order\ValueObject\OrderStatus;
use App\Domain\Order\ValueObject\StatusKind;
use App\Domain\Order\ValueObject\TaxRate;
use App\Domain\Shared\ValueObject\Money;

/**
 * 発注書の集約。金額の計算とステータス遷移を検証する。
 *
 * 移行前は金額をフロント (order-form.ts) が計算して hidden で送り、サーバーは
 * それをそのまま保存していた。同じ計算式をドメインに持たせている。
 */

function orderLine(int $quantity, int $unitCost, TaxRate $taxRate = TaxRate::Standard): OrderLine
{
    return new OrderLine(
        id: null,
        itemName: '商品',
        quantity: $quantity,
        unit: '個',
        unitCost: Money::fromInt($unitCost),
        taxRate: $taxRate,
    );
}

function newOrder(array $lines): Order
{
    return Order::create(
        customerId: 1,
        responsible: '担当者',
        honorTitle: '御中',
        issuedDate: new DateTimeImmutable('2026-09-01'),
        expDate: null,
        orderNo: OrderNo::fromString('NO-001'),
        title: '件名',
        remarks: null,
        lines: $lines,
    );
}

test('明細行の金額は数量×単価に税を足したもの', function () {
    $line = orderLine(2, 1000);

    expect($line->subtotal()->amount)->toBe(2000)
        ->and($line->tax()->amount)->toBe(200)
        ->and($line->total()->amount)->toBe(2200);
});

test('対象外の明細には消費税が付かない', function () {
    $line = orderLine(1, 1000, TaxRate::Exempt);

    expect($line->tax()->amount)->toBe(0)
        ->and($line->total()->amount)->toBe(1000);
});

test('合計は明細から導出される', function () {
    $order = newOrder([orderLine(1, 1000), orderLine(2, 250)]);

    expect($order->subtotal()->amount)->toBe(1500)
        ->and($order->tax()->amount)->toBe(150)
        ->and($order->total()->amount)->toBe(1650);
});

test('税区分が混ざっても税額を積み上げる', function () {
    $order = newOrder([
        orderLine(1, 1000, TaxRate::Standard),    // 税 100
        orderLine(1, 1000, TaxRate::ReducedFood), // 税 80
        orderLine(1, 1000, TaxRate::Exempt),      // 税 0
    ]);

    expect($order->subtotal()->amount)->toBe(3000)
        ->and($order->tax()->amount)->toBe(180)
        ->and($order->total()->amount)->toBe(3180);
});

test('明細が無ければ合計は0', function () {
    expect(newOrder([])->total()->amount)->toBe(0);
});

test('新規作成時のフラグは初期値になる', function () {
    $order = newOrder([]);

    expect($order->id())->toBeNull()
        ->and($order->issueStatus())->toBe(IssueStatus::NotIssued)
        ->and($order->orderStatus())->toBe(OrderStatus::NotOrdered)
        ->and($order->isDeleted())->toBeFalse()
        ->and($order->isConverted())->toBeFalse();
});

test('発行ステータスは押すたびに往復する', function () {
    $order = newOrder([]);

    $order->advanceStatus(StatusKind::Issue);
    expect($order->issueStatus())->toBe(IssueStatus::Issued);

    $order->advanceStatus(StatusKind::Issue);
    expect($order->issueStatus())->toBe(IssueStatus::NotIssued);
});

test('受注ステータスは3状態を巡回する', function () {
    $order = newOrder([]);

    foreach ([OrderStatus::Ordered, OrderStatus::Lost, OrderStatus::NotOrdered] as $expected) {
        $order->advanceStatus(StatusKind::Order);
        expect($order->orderStatus())->toBe($expected);
    }
});

test('ごみ箱への出し入れができる', function () {
    $order = newOrder([]);

    $order->trash();
    expect($order->isDeleted())->toBeTrue();

    $order->restore();
    expect($order->isDeleted())->toBeFalse();
});

test('更新してもステータスは維持される', function () {
    $order = newOrder([orderLine(1, 1000)]);
    $order->advanceStatus(StatusKind::Issue);
    $order->changeNote('社内メモ');

    $order->update(
        customerId: 2,
        responsible: '別の担当者',
        honorTitle: '様',
        issuedDate: new DateTimeImmutable('2026-10-01'),
        expDate: null,
        orderNo: OrderNo::fromString('NO-002'),
        title: '新しい件名',
        remarks: null,
        lines: [orderLine(1, 2000)],
    );

    expect($order->customerId())->toBe(2)
        ->and((string) $order->orderNo())->toBe('NO-002')
        ->and($order->total()->amount)->toBe(2200)
        // フォームが持たない項目は現在値のまま
        ->and($order->issueStatus())->toBe(IssueStatus::Issued)
        ->and($order->note())->toBe('社内メモ');
});

test('宛名は空の要素を詰めて組み立てる', function () {
    $order = Order::create(
        customerId: 1,
        responsible: null,
        honorTitle: '御中',
        issuedDate: new DateTimeImmutable('2026-09-01'),
        expDate: null,
        orderNo: OrderNo::fromString('NO-001'),
        title: null,
        remarks: null,
        lines: [],
    );

    expect($order->addresseeLine('株式会社テスト'))->toBe('株式会社テスト 御中');
});
