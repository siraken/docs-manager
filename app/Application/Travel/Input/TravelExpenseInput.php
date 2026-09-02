<?php

declare(strict_types=1);

namespace App\Application\Travel\Input;

use App\Application\Shared\DateParser;
use App\Domain\Shared\ValueObject\Money;
use App\Domain\Travel\Entity\TravelExpense;

final readonly class TravelExpenseInput
{
    public function __construct(
        public string $relId,
        public string $destination,
        public string $purpose,
        public mixed $applyDate,
        public mixed $dateFrom,
        public mixed $dateTo,
        public mixed $payDate,
        public string $applyPerson,
        public mixed $transportationFee,
        public mixed $accommodationFee,
        public mixed $gasFee,
        public mixed $dinnerFee,
        public mixed $lunchFee,
        public mixed $dailyAllowance,
    ) {
    }

    /**
     * CSV の 1 行から作る。先頭列は読み飛ばす。
     * 末尾の合計 ($row[15]) は読まない —— 内訳から計算し直すため。
     */
    public static function fromCsvRow(array $row): self
    {
        return new self(
            relId: (string) ($row[1] ?? ''),
            destination: (string) ($row[2] ?? ''),
            purpose: (string) ($row[3] ?? ''),
            applyDate: $row[4] ?? null,
            dateFrom: $row[5] ?? null,
            dateTo: $row[6] ?? null,
            payDate: $row[7] ?? null,
            applyPerson: (string) ($row[8] ?? ''),
            transportationFee: $row[9] ?? 0,
            accommodationFee: $row[10] ?? 0,
            gasFee: $row[11] ?? 0,
            dinnerFee: $row[12] ?? 0,
            lunchFee: $row[13] ?? 0,
            dailyAllowance: $row[14] ?? 0,
        );
    }

    public function toTravelExpense(): TravelExpense
    {
        return TravelExpense::create(
            relId: $this->relId,
            destination: $this->destination,
            purpose: $this->purpose,
            applyDate: DateParser::parse($this->applyDate, '申請日'),
            dateFrom: DateParser::parse($this->dateFrom, '出発日'),
            dateTo: DateParser::parse($this->dateTo, '帰着日'),
            payDate: DateParser::parse($this->payDate, '精算日'),
            applyPerson: $this->applyPerson,
            transportationFee: Money::fromNumeric($this->transportationFee),
            accommodationFee: Money::fromNumeric($this->accommodationFee),
            gasFee: Money::fromNumeric($this->gasFee),
            dinnerFee: Money::fromNumeric($this->dinnerFee),
            lunchFee: Money::fromNumeric($this->lunchFee),
            dailyAllowance: Money::fromNumeric($this->dailyAllowance),
        );
    }

    /** 既存の精算に上書きする */
    public function applyTo(TravelExpense $expense): void
    {
        $expense->update(
            relId: $this->relId,
            destination: $this->destination,
            purpose: $this->purpose,
            applyDate: DateParser::parse($this->applyDate, '申請日'),
            dateFrom: DateParser::parse($this->dateFrom, '出発日'),
            dateTo: DateParser::parse($this->dateTo, '帰着日'),
            payDate: DateParser::parse($this->payDate, '精算日'),
            applyPerson: $this->applyPerson,
            transportationFee: Money::fromNumeric($this->transportationFee),
            accommodationFee: Money::fromNumeric($this->accommodationFee),
            gasFee: Money::fromNumeric($this->gasFee),
            dinnerFee: Money::fromNumeric($this->dinnerFee),
            lunchFee: Money::fromNumeric($this->lunchFee),
            dailyAllowance: Money::fromNumeric($this->dailyAllowance),
        );
    }
}
