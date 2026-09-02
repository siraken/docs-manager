<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mapper;

use App\Application\Shared\DateParser;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\OrderLine;
use App\Domain\Order\ValueObject\IssueStatus;
use App\Domain\Order\ValueObject\OrderNo;
use App\Domain\Order\ValueObject\OrderStatus;
use App\Domain\Order\ValueObject\TaxRate;
use App\Domain\Shared\ValueObject\Money;
use App\Infrastructure\Persistence\Eloquent\Models\OrderDetail;
use App\Infrastructure\Persistence\Eloquent\Models\OrderHeader;

/**
 * order_headers / order_details と発注書集約の相互変換。
 *
 * sqlite (テスト) は integer カラムを文字列で返すことがあるため、
 * ドメインへ移す際に必ず型を寄せている。
 */
final class OrderMapper
{
    /** @param iterable<OrderDetail> $details */
    public static function toDomain(OrderHeader $header, iterable $details): Order
    {
        $lines = [];

        foreach ($details as $detail) {
            $lines[] = self::lineToDomain($detail);
        }

        return Order::reconstitute(
            id: (int) $header->id,
            customerId: (int) $header->customer_id,
            responsible: $header->responsible,
            honorTitle: $header->honor_title,
            issuedDate: DateParser::parse($header->issued_date, '発行日'),
            expDate: DateParser::parseNullable($header->exp_date, '有効期限'),
            orderNo: OrderNo::fromStorage((string) $header->order_no),
            title: $header->title,
            remarks: $header->remarks,
            issueStatus: IssueStatus::fromNullable($header->is_issued),
            orderStatus: OrderStatus::fromNullable($header->is_ordered),
            isDeleted: (int) $header->is_deleted === 1,
            isConverted: (int) $header->is_converted === 1,
            note: $header->note,
            lines: $lines,
        );
    }

    public static function lineToDomain(OrderDetail $detail): OrderLine
    {
        return new OrderLine(
            id: (int) $detail->id,
            itemName: (string) $detail->item_name,
            quantity: (int) $detail->quantity,
            unit: $detail->unit,
            unitCost: Money::fromNumeric($detail->cost ?? 0),
            taxRate: TaxRate::fromNullable($detail->tax_id),
        );
    }

    /**
     * ヘッダーの保存用属性。
     * 金額は明細から導出した値を書く (フォームから届いた合計は使わない)。
     *
     * @return array<string, mixed>
     */
    public static function toHeaderAttributes(Order $order): array
    {
        return [
            'customer_id' => $order->customerId(),
            'responsible' => $order->responsible(),
            'honor_title' => $order->honorTitle(),
            'issued_date' => $order->issuedDate()->format('Y-m-d'),
            'exp_date' => $order->expDate()?->format('Y-m-d'),
            'order_no' => (string) $order->orderNo(),
            'title' => $order->title(),
            'subtotal_price' => $order->subtotal()->amount,
            'tax_price' => $order->tax()->amount,
            'total_price' => $order->total()->amount,
            'remarks' => $order->remarks(),
            'is_issued' => $order->issueStatus()->value,
            'is_ordered' => $order->orderStatus()->value,
            'is_deleted' => $order->isDeleted() ? 1 : 0,
            'is_converted' => $order->isConverted() ? 1 : 0,
            'note' => $order->note(),
        ];
    }

    /**
     * 明細の保存用属性。price には税込金額が入る。
     *
     * @return array<string, mixed>
     */
    public static function toDetailAttributes(int $orderId, OrderLine $line): array
    {
        return [
            'slip_id' => $orderId,
            'item_name' => $line->itemName,
            'quantity' => $line->quantity,
            'unit' => $line->unit,
            'cost' => $line->unitCost->amount,
            'tax_id' => $line->taxRate->value,
            'price' => $line->total()->amount,
        ];
    }
}
