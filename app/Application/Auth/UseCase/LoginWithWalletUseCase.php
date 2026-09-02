<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCase;

use App\Application\Auth\Exception\AuthenticationFailedException;
use App\Application\Auth\Port\AuthSessionInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;

/**
 * MetaMask (ウォレットアドレス) でのログイン。
 *
 * FIXME: アドレスは公開情報なので、これは実質「知っていれば入れる」認証になっている。
 *        署名検証への差し替えは WalletAddress の TODO を参照。
 */
final readonly class LoginWithWalletUseCase
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AuthSessionInterface $session,
    ) {
    }

    public function execute(?string $address): User
    {
        $address = trim((string) $address);

        if ($address === '') {
            throw new AuthenticationFailedException('ウォレットアドレスが指定されていません。');
        }

        $user = $this->users->findByWalletAddress($address);

        if ($user === null || !$user->matchesWallet($address)) {
            throw new AuthenticationFailedException('ウォレットでのログインに失敗しました。');
        }

        $this->session->login($user);

        return $user;
    }
}
