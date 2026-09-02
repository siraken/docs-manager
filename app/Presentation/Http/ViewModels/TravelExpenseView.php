<?php

declare(strict_types=1);

namespace App\Presentation\Http\ViewModels;

use App\Domain\Travel\Entity\TravelExpense;
use Illuminate\Support\Collection;

final readonly class TravelExpenseView
{
    private function __construct(
        public ?int $id,
        public string $relId,
        public string $destination,
        public string $purpose,
        public string $applyDate,
        public string $dateFrom,
        public string $dateTo,
        public string $payDate,
        public string $applyPerson,
        public int $transportationFee,
        public int $accommodationFee,
        public int $gasFee,
        public int $dinnerFee,
        public int $lunchFee,
        public int $dailyAllowance,
        public int $totalFee,
        public string $totalFeeLabel,
    ) {
    }

    public static function fromEntity(TravelExpense $expense): self
    {
        return new self(
            id: $expense->id(),
            relId: $expense->relId(),
            destination: $expense->destination(),
            purpose: $expense->purpose(),
            applyDate: $expense->applyDate()->format('Y-m-d'),
            dateFrom: $expense->dateFrom()->format('Y-m-d'),
            dateTo: $expense->dateTo()->format('Y-m-d'),
            payDate: $expense->payDate()->format('Y-m-d'),
            applyPerson: $expense->applyPerson(),
            transportationFee: $expense->transportationFee()->amount,
            accommodationFee: $expense->accommodationFee()->amount,
            gasFee: $expense->gasFee()->amount,
            dinnerFee: $expense->dinnerFee()->amount,
            lunchFee: $expense->lunchFee()->amount,
            dailyAllowance: $expense->dailyAllowance()->amount,
            totalFee: $expense->totalFee()->amount,
            totalFeeLabel: $expense->totalFee()->format(),
        );
    }

    /** 新規作成フォーム用の空の入れ物 */
    public static function empty(): self
    {
        $today = date('Y-m-d');

        return new self(
            null, date('Ymd') . '-Num', '', '', $today, $today, $today, $today, '',
            0, 0, 0, 0, 0, 0, 0, '0',
        );
    }

    /**
     * @param list<TravelExpense> $expenses
     * @return Collection<int, self>
     */
    public static function collection(array $expenses): Collection
    {
        return collect($expenses)->map(self::fromEntity(...))->values();
    }

    public function shortPurpose(int $width = 30): string
    {
        return mb_strimwidth($this->purpose, 0, $width, '...');
    }
}
