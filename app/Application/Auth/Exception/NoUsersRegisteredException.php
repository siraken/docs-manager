<?php

declare(strict_types=1);

namespace App\Application\Auth\Exception;

use App\Domain\Shared\Exception\DomainException;

/**
 * ユーザーが 1 人も登録されていない状態。
 * Presentation 層はこれを受けてユーザー作成画面へ誘導する。
 */
final class NoUsersRegisteredException extends DomainException
{
    public function __construct()
    {
        parent::__construct('ユーザーが登録されていません。');
    }
}
