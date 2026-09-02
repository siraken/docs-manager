<?php

declare(strict_types=1);

namespace App\Infrastructure\Csv;

use App\Application\Order\Port\OrderCsvExporterInterface;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\OrderLine;

/**
 * 発注書のバックアップ CSV をメモリ上で組み立てる。
 *
 * 移行前はカレントディレクトリに実ファイルを書いてから readfile() し、
 * 明細が 1 件も無いと $details[0] の参照で落ちていた。
 * ここではヘッダー行を固定で持つため、明細が無くてもヘッダーだけの CSV が出る。
 */
final class OrderCsvExporter implements OrderCsvExporterInterface
{
    /** @var list<string> */
    private const COLUMNS = [
        'order_id',
        'order_no',
        'customer_id',
        'responsible',
        'honor_title',
        'issued_date',
        'exp_date',
        'title',
        'subtotal_price',
        'tax_price',
        'total_price',
        'remarks',
        'is_issued',
        'is_ordered',
        'is_deleted',
        'is_converted',
        'note',
        'item_name',
        'quantity',
        'unit',
        'cost',
        'tax_id',
        'price',
    ];

    public function export(Order $order): string
    {
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            throw new \RuntimeException('CSV の一時バッファを確保できませんでした。');
        }

        try {
            fputcsv($handle, self::COLUMNS, escape: '');

            $lines = $order->lines();

            if ($lines === []) {
                // 明細が無い発注書もヘッダー部分だけ出力する
                fputcsv($handle, $this->row($order, null), escape: '');
            }

            foreach ($lines as $line) {
                fputcsv($handle, $this->row($order, $line), escape: '');
            }

            rewind($handle);

            return (string) stream_get_contents($handle);
        } finally {
            fclose($handle);
        }
    }

    /** @return list<string|int|null> */
    private function row(Order $order, ?OrderLine $line): array
    {
        return [
            $order->id(),
            (string) $order->orderNo(),
            $order->customerId(),
            $order->responsible(),
            $order->honorTitle(),
            $order->issuedDate()->format('Y-m-d'),
            $order->expDate()?->format('Y-m-d'),
            $order->title(),
            $order->subtotal()->amount,
            $order->tax()->amount,
            $order->total()->amount,
            $order->remarks(),
            $order->issueStatus()->value,
            $order->orderStatus()->value,
            $order->isDeleted() ? 1 : 0,
            $order->isConverted() ? 1 : 0,
            $order->note(),
            $line?->itemName,
            $line?->quantity,
            $line?->unit,
            $line?->unitCost->amount,
            $line?->taxRate->value,
            $line?->total()->amount,
        ];
    }
}
