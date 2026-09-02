<?php

declare(strict_types=1);

namespace App\Application\User\UseCase;

use App\Application\User\Exception\InvalidTwoFactorCodeException;
use App\Application\User\Port\TwoFactorSetupStoreInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;

/**
 * 認証アプリが出したコードを検証し、通れば二段階認証を有効にする。
 *
 * 移行前は register_2fa_auth() が空のシークレットと空の QR URL を返すだけで、
 * 検証も保存も無かった (users.two_factor_secret_code カラムだけが存在した)。
 *
 * TODO: ログイン時に二段階認証を要求する経路はまだ無い。有効にしても
 *       いまはパスワードだけでログインできる。LoginWithPasswordUseCase に
 *       「2FA が有効なら確認画面へ」の分岐を足すこと。
 */
final readonly class ConfirmTwoFactorSetupUseCase
{
    public function __construct(
        private UserRepositoryInterface $users,
        private TwoFactorSetupStoreInterface $setupStore,
    ) {
    }

    public function execute(int $id, string $code, ?\DateTimeImmutable $now = null): User
    {
        $user = $this->users->findById($id)
            ?? throw EntityNotFoundException::of('ユーザー', $id);

        $secret = $this->setupStore->get($id)
            ?? throw new InvalidTwoFactorCodeException('設定用の情報が見つかりません。QR コードを表示し直してください。');

        if (!$secret->verify($code, $now ?? new \DateTimeImmutable())) {
            throw new InvalidTwoFactorCodeException();
        }

        $user->enableTwoFactor($secret);
        $saved = $this->users->save($user);

        $this->setupStore->forget($id);

        return $saved;
    }
}
