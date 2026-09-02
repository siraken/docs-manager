<?php

declare(strict_types=1);

namespace App\Application\User\UseCase;

use App\Application\User\Input\UpdateUserInput;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\PasswordHasherInterface;
use App\Domain\User\ValueObject\EmailAddress;
use App\Domain\User\ValueObject\NfcCredential;
use App\Domain\User\ValueObject\WalletAddress;

/**
 * ユーザーの更新。
 *
 * 移行前の実装は save() の後ろに「NFC をハッシュ化して再保存する」死んだ分岐が
 * 続いており (直前で return しているため到達しない)、平文で保存されていた。
 * NFC ログインが平文比較である以上ここも平文で保存する必要があるため、
 * 到達しないコードを消して意図を明示している (ハッシュ化は NfcCredential の TODO)。
 */
final readonly class UpdateUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $users,
        private PasswordHasherInterface $hasher,
    ) {
    }

    public function execute(int $id, UpdateUserInput $input): User
    {
        $user = $this->users->findById($id)
            ?? throw EntityNotFoundException::of('ユーザー', $id);

        $user->rename($input->name);
        $user->changeEmail(EmailAddress::fromString($input->email));

        // パスワード欄が空のときは現在のハッシュを維持する
        if ($input->wantsPasswordChange()) {
            $user->changePassword($this->hasher->hash((string) $input->password));
        }

        $user->changeNfcCredential($this->resolveNfcCredential($user, $input));

        $user->changeWalletAddress(
            $input->walletAddress === null || $input->walletAddress === ''
                ? null
                : WalletAddress::fromString($input->walletAddress),
        );

        return $this->users->save($user);
    }

    /**
     * シリアル番号が空なら NFC ログインを無効にする。
     * シリアル番号だけ変えて PIN 欄を空にした場合は、現在の PIN を引き継ぐ。
     */
    private function resolveNfcCredential(User $user, UpdateUserInput $input): ?NfcCredential
    {
        if ($input->nfcSerialNumber === null || $input->nfcSerialNumber === '') {
            return null;
        }

        $pin = $input->nfcPin === null || $input->nfcPin === ''
            ? $user->nfcCredential()?->pin
            : $input->nfcPin;

        if ($pin === null || $pin === '') {
            // PIN が一度も設定されていないカードは登録できない
            return null;
        }

        return NfcCredential::of($input->nfcSerialNumber, $pin);
    }
}
