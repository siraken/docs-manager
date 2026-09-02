<?php

declare(strict_types=1);

namespace App\Application\Travel\UseCase;

use App\Application\Travel\Input\TravelExpenseInput;
use App\Application\Travel\Port\CsvReaderInterface;
use App\Domain\Travel\Repository\TravelExpenseRepositoryInterface;

/**
 * 出張旅費精算の CSV 取り込み。列の並びは移行前と同じ ($row[1] から読む)。
 */
final readonly class ImportTravelExpenseCsvUseCase
{
    public function __construct(
        private TravelExpenseRepositoryInterface $expenses,
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

        $expenses = [];

        foreach ($rows as $row) {
            $expenses[] = TravelExpenseInput::fromCsvRow($row)->toTravelExpense();
        }

        return $this->expenses->saveAll($expenses);
    }
}
