<?php

declare(strict_types=1);

namespace App\Application\Learning\Input;

use App\Domain\Learning\ValueObject\SubmissionStatus;

/** 提出物の絞り込み条件 */
final readonly class SubmissionFilter
{
    private function __construct(
        public ?int $userId,
        public ?int $assignmentId,
        public ?string $status,
    ) {
    }

    public static function of(mixed $userId, mixed $assignmentId, mixed $status): self
    {
        $parsedStatus = is_string($status) && SubmissionStatus::tryFrom($status) !== null ? $status : null;

        return new self(self::intOrNull($userId), self::intOrNull($assignmentId), $parsedStatus);
    }

    private static function intOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }
}
