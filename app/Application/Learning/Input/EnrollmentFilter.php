<?php

declare(strict_types=1);

namespace App\Application\Learning\Input;

use App\Domain\Learning\ValueObject\EnrollmentStatus;

/** 受講記録の絞り込み条件 */
final readonly class EnrollmentFilter
{
    private function __construct(
        public ?int $userId,
        public ?int $courseId,
        public ?string $status,
    ) {
    }

    public static function of(mixed $userId, mixed $courseId, mixed $status): self
    {
        $parsedStatus = is_string($status) && EnrollmentStatus::tryFrom($status) !== null ? $status : null;

        return new self(self::intOrNull($userId), self::intOrNull($courseId), $parsedStatus);
    }

    private static function intOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }
}
