<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mapper;

use App\Application\Shared\DateParser;
use App\Domain\Shared\Exception\InvalidValueException;
use App\Domain\User\Entity\User as UserEntity;
use App\Domain\User\ValueObject\EmailAddress;
use App\Domain\User\ValueObject\HashedPassword;
use App\Domain\User\ValueObject\NfcCredential;
use App\Domain\User\ValueObject\TwoFactorSecret;
use App\Domain\User\ValueObject\WalletAddress;
use App\Infrastructure\Persistence\Eloquent\Models\User as UserModel;

final class UserMapper
{
    public static function toDomain(UserModel $model): UserEntity
    {
        return UserEntity::reconstitute(
            id: (int) $model->id,
            name: (string) $model->name,
            // 既存データに形式不正なアドレスが混じっていても一覧が落ちないよう
            // 復元では検証しない (EmailAddress::fromStorage の TODO 参照)
            email: EmailAddress::fromStorage((string) $model->email),
            password: HashedPassword::fromHash((string) $model->password),
            nfcCredential: self::nfcCredential($model),
            walletAddress: self::walletAddress($model),
            twoFactorSecret: self::twoFactorSecret($model),
            updatedAt: DateParser::parseNullable($model->updated_at, '更新日時'),
        );
    }

    /** @return array<string, mixed> */
    public static function toAttributes(UserEntity $user): array
    {
        return [
            'name' => $user->name(),
            'email' => (string) $user->email(),
            'password' => (string) $user->password(),
            'nfc_serial_number' => $user->nfcCredential()?->serialNumber,
            'nfc_pin' => $user->nfcCredential()?->pin,
            'wallet_address' => (string) $user->walletAddress() === '' ? null : (string) $user->walletAddress(),
            'two_factor_secret_code' => $user->twoFactorSecret() === null ? null : (string) $user->twoFactorSecret(),
        ];
    }

    private static function nfcCredential(UserModel $model): ?NfcCredential
    {
        $serial = $model->nfc_serial_number;
        $pin = $model->nfc_pin;

        if ($serial === null || $serial === '' || $pin === null || $pin === '') {
            return null;
        }

        return NfcCredential::of($serial, $pin);
    }

    private static function walletAddress(UserModel $model): ?WalletAddress
    {
        $address = $model->wallet_address;

        return $address === null || $address === '' ? null : WalletAddress::fromStorage($address);
    }

    private static function twoFactorSecret(UserModel $model): ?TwoFactorSecret
    {
        $secret = $model->two_factor_secret_code;

        if ($secret === null || $secret === '') {
            return null;
        }

        try {
            return TwoFactorSecret::fromString($secret);
        } catch (InvalidValueException) {
            // 移行前は空文字を書き込むスタブがあったため、Base32 でない値が
            // 残っている可能性がある。その場合は「未設定」として扱う。
            return null;
        }
    }
}
