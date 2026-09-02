<?php

declare(strict_types=1);

namespace App\Application\Order\UseCase;

use App\Application\Shared\Output\RenderedDocument;
use App\Application\Order\Port\OrderCsvExporterInterface;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * 発注書のバックアップ CSV を返す。
 *
 * 移行前の実装には 3 つの問題があった。
 *  - $details[0] を無条件に参照するため、明細が無いと ErrorException で落ちた
 *  - カレントディレクトリに order_no 由来の名前で実ファイルを作っていた
 *    (番号は検証されておらず、パス区切りを書き込める状態だった)
 *  - Laravel の Response ではなく素の header() + readfile() で出力していたため
 *    テストから検証できなかった
 * いずれも解消してある。
 */
final readonly class ExportOrderCsvUseCase
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private OrderCsvExporterInterface $exporter,
    ) {
    }

    public function execute(int $id): RenderedDocument
    {
        $order = $this->orders->findById($id)
            ?? throw EntityNotFoundException::of('発注書', $id);

        return new RenderedDocument(
            fileName: $order->orderNo()->toFileName('csv'),
            contentType: 'text/csv; charset=UTF-8',
            contents: $this->exporter->export($order),
        );
    }
}
