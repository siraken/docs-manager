<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCase;

use App\Application\Auth\Exception\AuthenticationFailedException;
use App\Application\Auth\Port\AuthSessionInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;

/**
 * NFC カードでのログイン。
 *
 * シリアル番号でユーザーを引き、PIN の照合はエンティティ側 (hash_equals) で行う。
 * 移行前は where(serial)->where(pin) の 1 クエリで照合していたため、
 * PIN の比較が SQL の等価比較になっていた。
 */
final readonly class LoginWithNfcUseCase
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AuthSessionInterface $session,
    ) {
    }

    public function execute(?string $serialNumber, ?string $pin): User
    {
        $serialNumber = trim((string) $serialNumber);
        $pin = trim((string) $pin);

        if ($serialNumber === '' || $pin === '') {
            throw new AuthenticationFailedException('NFC の情報が不足しています。');
        }

        $user = $this->users->findByNfcSerialNumber($serialNumber);

        if ($user === null || !$user->matchesNfc($serialNumber, $pin)) {
            throw new AuthenticationFailedException('NFC でのログインに失敗しました。');
        }

        $this->session->login($user);

        return $user;
    }
}
