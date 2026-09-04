<?php

declare(strict_types=1);

namespace App\Domain\Project\Entity;

use App\Domain\Project\ValueObject\JiraKey;
use App\Domain\Project\ValueObject\ProjectStatus;
use App\Domain\Shared\ValueObject\Money;

/**
 * 案件。売上分析の集計対象でもある。
 */
final class Project
{
    private function __construct(
        private ?int $id,
        private string $name,
        private ?string $description,
        private ?int $clientId,
        private ?JiraKey $jiraKey,
        private ?\DateTimeImmutable $startDate,
        private ?\DateTimeImmutable $endDate,
        private ?\DateTimeImmutable $paymentDate,
        private Money $price,
        private ProjectStatus $status,
    ) {
    }

    public static function create(
        string $name,
        ?string $description,
        ?int $clientId,
        ?JiraKey $jiraKey,
        ?\DateTimeImmutable $startDate,
        ?\DateTimeImmutable $endDate,
        ?\DateTimeImmutable $paymentDate,
        Money $price,
        ProjectStatus $status,
    ): self {
        return new self(null, $name, $description, $clientId, $jiraKey, $startDate, $endDate, $paymentDate, $price, $status);
    }

    public static function reconstitute(
        int $id,
        string $name,
        ?string $description,
        ?int $clientId,
        ?JiraKey $jiraKey,
        ?\DateTimeImmutable $startDate,
        ?\DateTimeImmutable $endDate,
        ?\DateTimeImmutable $paymentDate,
        Money $price,
        ProjectStatus $status,
    ): self {
        return new self($id, $name, $description, $clientId, $jiraKey, $startDate, $endDate, $paymentDate, $price, $status);
    }

    public function update(
        string $name,
        ?string $description,
        ?int $clientId,
        ?JiraKey $jiraKey,
        ?\DateTimeImmutable $startDate,
        ?\DateTimeImmutable $endDate,
        ?\DateTimeImmutable $paymentDate,
        Money $price,
        ProjectStatus $status,
    ): void {
        $this->name = $name;
        $this->description = $description;
        $this->clientId = $clientId;
        $this->jiraKey = $jiraKey;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->paymentDate = $paymentDate;
        $this->price = $price;
        $this->status = $status;
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

    public function description(): ?string
    {
        return $this->description;
    }

    public function clientId(): ?int
    {
        return $this->clientId;
    }

    public function jiraKey(): ?JiraKey
    {
        return $this->jiraKey;
    }

    public function startDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function endDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function paymentDate(): ?\DateTimeImmutable
    {
        return $this->paymentDate;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function status(): ProjectStatus
    {
        return $this->status;
    }
}
