<?php

declare(strict_types=1);

namespace App\Application\Learning\Input;

use App\Application\Shared\DateParser;
use App\Domain\Learning\ValueObject\EnrollmentStatus;

final readonly class EnrollmentInput
{
    public function __construct(
        public int $userId,
        public int $courseId,
        public mixed $status,
        public mixed $startedAt,
        public mixed $completedAt,
        public ?string $note,
    ) {
    }

    public function statusValue(): EnrollmentStatus
    {
        return EnrollmentStatus::fromNullable($this->status);
    }

    public function startedAtValue(): ?\DateTimeImmutable
    {
        return DateParser::parseNullable($this->startedAt, '受講開始日');
    }

    public function completedAtValue(): ?\DateTimeImmutable
    {
        return DateParser::parseNullable($this->completedAt, '完了日');
    }
}
