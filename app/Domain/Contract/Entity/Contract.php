<?php

declare(strict_types=1);

namespace App\Domain\Contract\Entity;

use App\Domain\Contract\ValueObject\ContractStatus;
use App\Domain\Contract\ValueObject\ContractTerm;

/**
 * 契約。取引先 (customers) と紐付き、期間を持つ。
 *
 * 移植元は「契約」と称しながらフォームが案件のものの丸写しで、
 * 存在しないカラム (pid) を送り、テーブルにある契約番号 (contract_id) は
 * どこからも入力できなかった。ここでは契約番号を第一級の項目として扱う。
 */
final class Contract
{
    private function __construct(
        private ?int $id,
        private string $name,
        private ?string $contractNo,
        private ?int $customerId,
        private ContractTerm $term,
        private ?string $description,
    ) {
    }

    public static function create(
        string $name,
        ?string $contractNo,
        ?int $customerId,
        ContractTerm $term,
        ?string $description,
    ): self {
        return new self(null, $name, $contractNo, $customerId, $term, $description);
    }

    public static function reconstitute(
        int $id,
        string $name,
        ?string $contractNo,
        ?int $customerId,
        ContractTerm $term,
        ?string $description,
    ): self {
        return new self($id, $name, $contractNo, $customerId, $term, $description);
    }

    public function update(
        string $name,
        ?string $contractNo,
        ?int $customerId,
        ContractTerm $term,
        ?string $description,
    ): void {
        $this->name = $name;
        $this->contractNo = $contractNo;
        $this->customerId = $customerId;
        $this->term = $term;
        $this->description = $description;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function contractNo(): ?string
    {
        return $this->contractNo;
    }

    public function customerId(): ?int
    {
        return $this->customerId;
    }

    public function term(): ContractTerm
    {
        return $this->term;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function statusOn(\DateTimeImmutable $date): ContractStatus
    {
        return $this->term->statusOn($date);
    }
}
