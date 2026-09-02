<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\EmailAddress;
use App\Infrastructure\Persistence\Eloquent\Mapper\UserMapper;
use App\Infrastructure\Persistence\Eloquent\Models\User as UserModel;

final class UserRepository implements UserRepositoryInterface
{
    /** @return list<User> */
    public function listAll(): array
    {
        return UserModel::orderBy('id')->get()
            ->map(UserMapper::toDomain(...))
            ->all();
    }

    public function findById(int $id): ?User
    {
        $model = UserModel::find($id);

        return $model === null ? null : UserMapper::toDomain($model);
    }

    public function findByEmail(EmailAddress $email): ?User
    {
        $model = UserModel::where('email', (string) $email)->first();

        return $model === null ? null : UserMapper::toDomain($model);
    }

    public function findByNfcSerialNumber(string $serialNumber): ?User
    {
        $model = UserModel::where('nfc_serial_number', $serialNumber)->first();

        return $model === null ? null : UserMapper::toDomain($model);
    }

    public function findByWalletAddress(string $address): ?User
    {
        // 保存されている値の大文字小文字が揃っていないため、まず完全一致で探し、
        // 見つからなければ小文字に正規化した比較で探す。
        $model = UserModel::where('wallet_address', $address)->first()
            ?? UserModel::whereRaw('LOWER(wallet_address) = ?', [strtolower(trim($address))])->first();

        return $model === null ? null : UserMapper::toDomain($model);
    }

    public function count(): int
    {
        return UserModel::count();
    }

    public function save(User $user): User
    {
        $model = $user->id() === null
            ? new UserModel()
            : UserModel::find($user->id()) ?? new UserModel();

        $model->fill(UserMapper::toAttributes($user));
        $model->save();

        $user->assignId((int) $model->id);

        return $user;
    }

    public function delete(int $id): void
    {
        UserModel::where('id', $id)->delete();
    }
}
