<?php

declare(strict_types=1);

namespace App\Application\Travel\UseCase;

use App\Application\Travel\Input\TravelInput;
use App\Application\Travel\Port\CsvReaderInterface;
use App\Domain\Travel\Repository\TravelRepositoryInterface;

/**
 * 出張申請の CSV 取り込み。
 *
 * 列の並びは移行前と同じで、先頭列 ($row[0]) は使わず $row[1] から読む。
 * 取り込みは 1 トランザクションで、1 行でも壊れていれば全体を取り消す
 * (移行前は 1 行ずつ save していたため、途中で失敗すると半端に入った)。
 */
final readonly class ImportTravelCsvUseCase
{
    public function __construct(
        private TravelRepositoryInterface $travels,
        private CsvReaderInterface $csvReader,
    ) {
    }

    /** @return int 取り込んだ件数 */
    public function execute(string $path, bool $hasHeaderRow): int
    {
        $rows = $this->csvReader->read($path);

        if ($hasHeaderRow) {
            array_shift($rows);
        }

        $travels = [];

        foreach ($rows as $row) {
            $travels[] = TravelInput::fromCsvRow($row)->toTravel();
        }

        return $this->travels->saveAll($travels);
    }
}
