<?php

declare(strict_types=1);

namespace App\Application\Learning\Input;

use App\Application\Shared\DateParser;
use App\Domain\Learning\ValueObject\SubmissionStatus;

final readonly class SubmissionInput
{
    public function __construct(
        public int $assignmentId,
        public int $userId,
        public mixed $status,
        public mixed $submittedAt,
        public ?string $body,
        public ?string $feedback,
    ) {
    }

    public function statusValue(): SubmissionStatus
    {
        return SubmissionStatus::fromNullable($this->status);
    }

    public function submittedAtValue(): ?\DateTimeImmutable
    {
        return DateParser::parseNullable($this->submittedAt, '提出日');
    }
}
