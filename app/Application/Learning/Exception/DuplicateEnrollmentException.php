<?php

declare(strict_types=1);

namespace App\Application\Learning\Exception;

use App\Domain\Shared\Exception\DomainException;

/**
 * 同じ受講者・同じ講座の記録を 2 つ作ろうとした。
 *
 * 受け直しは既存の記録を更新する形にしている。2 行あると
 * 「完了したのか受講中なのか」が決められない。
 */
final class DuplicateEnrollmentException extends DomainException
{
    public static function of(string $userName, string $courseTitle): self
    {
        return new self(sprintf(
            '%s さんの「%s」の受講記録は既にあります。既存の記録を編集してください。',
            $userName,
            $courseTitle,
        ));
    }
}
