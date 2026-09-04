<?php

declare(strict_types=1);

namespace App\Application\Chat\Exception;

use App\Domain\Shared\Exception\DomainException;

/**
 * 他人の発言を消そうとした。
 */
final class CannotDeleteOthersMessageException extends DomainException
{
    public static function create(): self
    {
        return new self('自分の発言だけ削除できます。');
    }
}
