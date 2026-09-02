<?php

declare(strict_types=1);

namespace App\Application\SpreadSheet\Port;

/**
 * Google スプレッドシートへの追記。実装は Infrastructure 層。
 */
interface SpreadSheetWriterInterface
{
    /**
     * シート末尾に 1 行追加する。
     *
     * @param list<scalar|null> $values
     */
    public function appendRow(array $values): void;
}
