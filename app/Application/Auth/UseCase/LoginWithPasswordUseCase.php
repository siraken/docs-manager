<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCase;

use App\Application\Auth\Exception\AuthenticationFailedException;
use App\Application\Auth\Exception\NoUsersRegisteredException;
use App\Application\Auth\Port\AuthSessionInterface;
use App\Application\Auth\Port\LoginContext;
use App\Application\Auth\Port\LoginNotifierInterface;
use App\Domain\Shared\Exception\InvalidValueException;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\PasswordHasherInterface;
use App\Domain\User\ValueObject\EmailAddress;

/**
 * メールアドレスとパスワードでのログイン。
 *
 * TODO: 二段階認証を有効にしていても、ここではコードを要求していない。
 *       2FA の設定 (ConfirmTwoFactorSetupUseCase) は動くが、ログイン経路への
 *       組み込みが残っている。
 * TODO: 試行回数の制限が無い。web ルートには throttle が掛かっていないため、
 *       ログインだけでもレート制限を入れること。
 */
final readonly class LoginWithPasswordUseCase
{
    public function __construct(
        private UserRepositoryInterface $users,
        private PasswordHasherInterface $hasher,
        private AuthSessionInterface $session,
        private LoginNotifierInterface $notifier,
    ) {
    }

    public function execute(?string $email, ?string $password, LoginContext $context): User
    {
        if ($this->users->count() === 0) {
            throw new NoUsersRegisteredException();
        }

        try {
            $emailAddress = EmailAddress::fromString($email);
        } catch (InvalidValueException) {
            // 形式が不正なだけでも「存在しない」と同じ扱いにする
            throw new AuthenticationFailedException();
        }

        $user = $this->users->findByEmail($emailAddress);

        if ($user === null || !$user->verifyPassword((string) $password, $this->hasher)) {
            throw new AuthenticationFailedException();
        }

        $this->session->login($user);
        $this->notifier->notifyLogin($user, $context);

        return $user;
    }
}
