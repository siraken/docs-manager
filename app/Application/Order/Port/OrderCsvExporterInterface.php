<?php

declare(strict_types=1);

namespace App\Application\Order\Port;

use App\Domain\Order\Entity\Order;

/**
 * 発注書のバックアップ CSV 生成。
 *
 * 移行前はカレントディレクトリに実ファイルを書いて readfile() してから
 * unlink() していた。実装はメモリ上で組み立てて文字列を返す。
 */
interface OrderCsvExporterInterface
{
    public function export(Order $order): string;
}
