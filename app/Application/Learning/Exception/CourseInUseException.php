<?php

declare(strict_types=1);

namespace App\Application\Learning\Exception;

use App\Domain\Shared\Exception\DomainException;

/**
 * 受講記録のある講座を削除しようとした。
 *
 * 消すと記録が宙に浮くので、使わなくなった講座は「下書きに戻す」で
 * 選択肢から外す。
 */
final class CourseInUseException extends DomainException
{
    public static function of(string $title): self
    {
        return new self(sprintf(
            '「%s」には受講記録があるため削除できません。使わなくなった講座は「公開しない」で選択肢から外せます。',
            $title,
        ));
    }
}
