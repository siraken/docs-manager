<?php

declare(strict_types=1);

namespace App\Application\User\UseCase;

use App\Application\User\Output\TwoFactorSetup;
use App\Application\User\Port\TwoFactorSetupStoreInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\TwoFactorSecret;

/**
 * 二段階認証の設定を開始する。
 *
 * シークレットを発行して一時保管し、認証アプリ用の otpauth URI を返す。
 * この時点ではユーザーのレコードに書かない (コードの検証が通ってから有効化する)。
 */
final readonly class StartTwoFactorSetupUseCase
{
    /** 認証アプリの一覧に出るサービス名 */
    private const ISSUER = 'Novalumo Docs Manager';

    public function __construct(
        private UserRepositoryInterface $users,
        private TwoFactorSetupStoreInterface $setupStore,
    ) {
    }

    public function execute(int $id): TwoFactorSetup
    {
        $user = $this->users->findById($id)
            ?? throw EntityNotFoundException::of('ユーザー', $id);

        $secret = TwoFactorSecret::generate();
        $this->setupStore->put($id, $secret);

        return new TwoFactorSetup(
            secret: $secret,
            uri: $secret->toUri(self::ISSUER, (string) $user->email()),
            alreadyEnabled: $user->hasTwoFactorEnabled(),
        );
    }
}
