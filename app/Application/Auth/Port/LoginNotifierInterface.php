<?php

declare(strict_types=1);

namespace App\Application\Auth\Port;

use App\Domain\User\Entity\User;

/**
 * ログイン通知。実装はメール送信 (Infrastructure 層)。
 */
interface LoginNotifierInterface
{
    public function notifyLogin(User $user, LoginContext $context): void;
}
