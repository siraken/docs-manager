<?php

declare(strict_types=1);

namespace App\Domain\Travel\Entity;

use App\Domain\Shared\ValueObject\Money;

/**
 * 出張旅費精算。費目ごとの金額を持ち、合計はそれらの和として導出する。
 *
 * 移行前は合計 (total_fee) をフォームや CSV から受け取った値のまま保存していたが、
 * 内訳と合計が食い違いうるため、ここで導出した値を保存する。
 */
final class TravelExpense
{
    private function __construct(
        private ?int $id,
        private string $relId,
        private string $destination,
        private string $purpose,
        private \DateTimeImmutable $applyDate,
        private \DateTimeImmutable $dateFrom,
        private \DateTimeImmutable $dateTo,
        private \DateTimeImmutable $payDate,
        private string $applyPerson,
        private Money $transportationFee,
        private Money $accommodationFee,
        private Money $gasFee,
        private Money $dinnerFee,
        private Money $lunchFee,
        private Money $dailyAllowance,
    ) {
    }

    public static function create(
        string $relId,
        string $destination,
        string $purpose,
        \DateTimeImmutable $applyDate,
        \DateTimeImmutable $dateFrom,
        \DateTimeImmutable $dateTo,
        \DateTimeImmutable $payDate,
        string $applyPerson,
        Money $transportationFee,
        Money $accommodationFee,
        Money $gasFee,
        Money $dinnerFee,
        Money $lunchFee,
        Money $dailyAllowance,
    ): self {
        return new self(
            null, $relId, $destination, $purpose, $applyDate, $dateFrom, $dateTo, $payDate, $applyPerson,
            $transportationFee, $accommodationFee, $gasFee, $dinnerFee, $lunchFee, $dailyAllowance,
        );
    }

    public static function reconstitute(
        int $id,
        string $relId,
        string $destination,
        string $purpose,
        \DateTimeImmutable $applyDate,
        \DateTimeImmutable $dateFrom,
        \DateTimeImmutable $dateTo,
        \DateTimeImmutable $payDate,
        string $applyPerson,
        Money $transportationFee,
        Money $accommodationFee,
        Money $gasFee,
        Money $dinnerFee,
        Money $lunchFee,
        Money $dailyAllowance,
    ): self {
        return new self(
            $id, $relId, $destination, $purpose, $applyDate, $dateFrom, $dateTo, $payDate, $applyPerson,
            $transportationFee, $accommodationFee, $gasFee, $dinnerFee, $lunchFee, $dailyAllowance,
        );
    }

    public function update(
        string $relId,
        string $destination,
        string $purpose,
        \DateTimeImmutable $applyDate,
        \DateTimeImmutable $dateFrom,
        \DateTimeImmutable $dateTo,
        \DateTimeImmutable $payDate,
        string $applyPerson,
        Money $transportationFee,
        Money $accommodationFee,
        Money $gasFee,
        Money $dinnerFee,
        Money $lunchFee,
        Money $dailyAllowance,
    ): void {
        $this->relId = $relId;
        $this->destination = $destination;
        $this->purpose = $purpose;
        $this->applyDate = $applyDate;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->payDate = $payDate;
        $this->applyPerson = $applyPerson;
        $this->transportationFee = $transportationFee;
        $this->accommodationFee = $accommodationFee;
        $this->gasFee = $gasFee;
        $this->dinnerFee = $dinnerFee;
        $this->lunchFee = $lunchFee;
        $this->dailyAllowance = $dailyAllowance;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function relId(): string
    {
        return $this->relId;
    }

    public function destination(): string
    {
        return $this->destination;
    }

    public function purpose(): string
    {
        return $this->purpose;
    }

    public function applyDate(): \DateTimeImmutable
    {
        return $this->applyDate;
    }

    public function dateFrom(): \DateTimeImmutable
    {
        return $this->dateFrom;
    }

    public function dateTo(): \DateTimeImmutable
    {
        return $this->dateTo;
    }

    public function payDate(): \DateTimeImmutable
    {
        return $this->payDate;
    }

    public function applyPerson(): string
    {
        return $this->applyPerson;
    }

    public function transportationFee(): Money
    {
        return $this->transportationFee;
    }

    public function accommodationFee(): Money
    {
        return $this->accommodationFee;
    }

    public function gasFee(): Money
    {
        return $this->gasFee;
    }

    public function dinnerFee(): Money
    {
        return $this->dinnerFee;
    }

    public function lunchFee(): Money
    {
        return $this->lunchFee;
    }

    public function dailyAllowance(): Money
    {
        return $this->dailyAllowance;
    }

    /** 合計。内訳から導出する */
    public function totalFee(): Money
    {
        return $this->transportationFee
            ->add($this->accommodationFee)
            ->add($this->gasFee)
            ->add($this->dinnerFee)
            ->add($this->lunchFee)
            ->add($this->dailyAllowance);
    }
}
