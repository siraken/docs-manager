<?php

declare(strict_types=1);

namespace App\Application\Learning\Exception;

use App\Domain\Shared\Exception\DomainException;

/**
 * 同じ提出者・同じ課題の提出物を 2 つ作ろうとした。
 *
 * 再提出は既存の行を更新する形にしている。2 行あると
 * 「合格なのか差し戻しなのか」が決められない。
 */
final class DuplicateSubmissionException extends DomainException
{
    public static function of(string $userName, string $assignmentTitle): self
    {
        return new self(sprintf(
            '%s さんの「%s」の提出物は既にあります。既存の提出物を編集してください。',
            $userName,
            $assignmentTitle,
        ));
    }
}
