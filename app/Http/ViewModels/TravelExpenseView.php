<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Travel\Entity\TravelExpense;
use Illuminate\Support\Collection;

final readonly class TravelExpenseView implements \JsonSerializable
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
     * Inertia の props 用。
     *
     * 費目は入力欄が金額そのままなので整形しない (フォームの value に入る)。
     * 表示用に桁区切りが要る場所は画面側で組む。
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'relId' => $this->relId,
            'destination' => $this->destination,
            'purpose' => $this->purpose,
            'applyDate' => $this->applyDate,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'payDate' => $this->payDate,
            'applyPerson' => $this->applyPerson,
            'transportationFee' => $this->transportationFee,
            'accommodationFee' => $this->accommodationFee,
            'gasFee' => $this->gasFee,
            'dinnerFee' => $this->dinnerFee,
            'lunchFee' => $this->lunchFee,
            'dailyAllowance' => $this->dailyAllowance,
            'totalFee' => $this->totalFee,
            'totalFeeLabel' => $this->totalFeeLabel,

            'shortPurpose' => $this->shortPurpose(),

            'urls' => $this->id === null ? null : [
                'show' => route('expenses.view', ['id' => $this->id]),
                'edit' => route('expenses.edit', ['id' => $this->id]),
                'pdf' => route('expenses.pdf', ['id' => $this->id]),
            ],
        ];
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
