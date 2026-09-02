<?php

declare(strict_types=1);

namespace App\Application\Travel\Port;

/**
 * CSV ファイルを行の配列として読む。実装は Infrastructure 層 (SplFileObject)。
 */
interface CsvReaderInterface
{
    /**
     * @return list<list<string|null>> 1 行 = 1 要素。空行は含まない
     */
    public function read(string $path): array;
}
