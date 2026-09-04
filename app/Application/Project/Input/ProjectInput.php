<?php

declare(strict_types=1);

namespace App\Application\Project\Input;

use App\Application\Shared\DateParser;
use App\Domain\Project\ValueObject\JiraKey;
use App\Domain\Project\ValueObject\ProjectStatus;
use App\Domain\Shared\ValueObject\Money;

final readonly class ProjectInput
{
    public function __construct(
        public string $name,
        public ?string $description,
        public ?int $clientId,
        public mixed $jiraKey,
        public mixed $startDate,
        public mixed $endDate,
        public mixed $paymentDate,
        public mixed $price,
        public mixed $status,
    ) {
    }

    /** 書式の検証は JiraKey が持つ */
    public function jiraKeyValue(): ?JiraKey
    {
        return JiraKey::parseNullable($this->jiraKey);
    }

    public function startDateValue(): ?\DateTimeImmutable
    {
        return DateParser::parseNullable($this->startDate, '開始日');
    }

    public function endDateValue(): ?\DateTimeImmutable
    {
        return DateParser::parseNullable($this->endDate, '終了日');
    }

    public function paymentDateValue(): ?\DateTimeImmutable
    {
        return DateParser::parseNullable($this->paymentDate, '支払日');
    }

    public function priceValue(): Money
    {
        return Money::fromNumeric($this->price);
    }

    public function statusValue(): ProjectStatus
    {
        return ProjectStatus::fromNullable($this->status);
    }
}
