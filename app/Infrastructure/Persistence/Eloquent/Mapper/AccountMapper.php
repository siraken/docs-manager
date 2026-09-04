<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mapper;

use App\Domain\Accounting\Entity\Account as AccountEntity;
use App\Domain\Accounting\ValueObject\AccountType;
use App\Infrastructure\Persistence\Eloquent\Models\Account as AccountModel;

final class AccountMapper
{
    public static function toDomain(AccountModel $model): AccountEntity
    {
        return AccountEntity::reconstitute(
            id: (int) $model->id,
            name: (string) $model->name,
            code: $model->code,
            type: AccountType::tryFrom((string) $model->type) ?? AccountType::Asset,
            // sqlite は boolean を 0 / 1 の整数 (さらに文字列) で返す
            isActive: (bool) $model->is_active,
            note: $model->note,
        );
    }

    /** @return array<string, mixed> */
    public static function toAttributes(AccountEntity $account): array
    {
        return [
            'code' => $account->code(),
            'name' => $account->name(),
            'type' => $account->type()->value,
            'is_active' => $account->isActive(),
            'note' => $account->note(),
        ];
    }
}
