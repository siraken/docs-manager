<?php

declare(strict_types=1);

namespace App\Application\Learning\Exception;

use App\Domain\Shared\Exception\DomainException;

/**
 * 提出物のある課題を削除しようとした。
 */
final class AssignmentInUseException extends DomainException
{
    public static function of(string $title): self
    {
        return new self(sprintf('「%s」には提出物があるため削除できません。', $title));
    }
}
