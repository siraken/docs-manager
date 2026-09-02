<?php

declare(strict_types=1);

namespace App\Application\User\Exception;

use App\Domain\Shared\Exception\DomainException;

/** 最後のユーザーを消すと誰もログインできなくなるため拒否する */
final class CannotDeleteLastUserException extends DomainException
{
    public function __construct()
    {
        parent::__construct('最後のユーザーは削除できません。');
    }
}
