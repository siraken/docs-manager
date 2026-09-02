<?php

declare(strict_types=1);

namespace App\Application\User\Exception;

use App\Domain\Shared\Exception\DomainException;

final class InvalidTwoFactorCodeException extends DomainException
{
    public function __construct(string $message = '認証コードが正しくありません。')
    {
        parent::__construct($message);
    }
}
