<?php

declare(strict_types=1);

namespace App\Application\User\UseCase;

use App\Application\User\Input\NfcRegistrationInput;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\NfcCredential;

/**
 * NFC カードの登録。
 *
 * 移行前は登録時だけ Hash::make() でハッシュ化していたのに、ログイン時は
 * 平文で完全一致を見ていたため、この経路で登録したカードではログインできなかった。
 * 照合方式に合わせて平文で保存する (ハッシュ化は NfcCredential の TODO 参照)。
 */
final readonly class RegisterNfcCredentialUseCase
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    public function execute(int $id, NfcRegistrationInput $input): User
    {
        $user = $this->users->findById($id)
            ?? throw EntityNotFoundException::of('ユーザー', $id);

        $user->changeNfcCredential(NfcCredential::of($input->serialNumber, $input->pin));

        return $this->users->save($user);
    }
}
