<?php

declare(strict_types=1);

namespace App\Infrastructure\Csv;

use App\Application\Travel\Port\CsvReaderInterface;
use SplFileObject;

/**
 * SplFileObject の READ_CSV で CSV を読む。
 * フラグの組み合わせは移行前と同じで、読み取り結果が変わらないようにしている。
 */
final class SplFileObjectCsvReader implements CsvReaderInterface
{
    /** @return list<list<string|null>> */
    public function read(string $path): array
    {
        $file = new SplFileObject($path);
        $file->setFlags(
            SplFileObject::READ_CSV
            | SplFileObject::READ_AHEAD
            | SplFileObject::SKIP_EMPTY
            | SplFileObject::DROP_NEW_LINE,
        );

        $rows = [];

        foreach ($file as $row) {
            if (!is_array($row)) {
                continue;
            }

            // SKIP_EMPTY を付けても、末尾の空行が [null] として現れることがある
            if ($row === [null]) {
                continue;
            }

            $rows[] = $row;
        }

        return $rows;
    }
}
