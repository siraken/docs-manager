<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Accounting\Entity\Account;
use App\Domain\Accounting\Repository\AccountRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Mapper\AccountMapper;
use App\Infrastructure\Persistence\Eloquent\Models\Account as AccountModel;
use App\Infrastructure\Persistence\Eloquent\Models\JournalEntry as JournalEntryModel;

final class AccountRepository implements AccountRepositoryInterface
{
    /** @return list<Account> */
    public function listAll(): array
    {
        return $this->ordered(AccountModel::query())->map(AccountMapper::toDomain(...))->all();
    }

    /** @return list<Account> */
    public function listActive(): array
    {
        return $this->ordered(AccountModel::where('is_active', true))
            ->map(AccountMapper::toDomain(...))
            ->all();
    }

    public function findById(int $id): ?Account
    {
        $model = AccountModel::find($id);

        return $model === null ? null : AccountMapper::toDomain($model);
    }

    public function save(Account $account): Account
    {
        $model = $account->id() === null
            ? new AccountModel()
            : AccountModel::find($account->id()) ?? new AccountModel();

        $model->fill(AccountMapper::toAttributes($account));
        $model->save();

        $account->assignId((int) $model->id);

        return $account;
    }

    public function delete(int $id): void
    {
        AccountModel::destroy($id);
    }

    public function isReferenced(int $id): bool
    {
        return JournalEntryModel::where('debit_account_id', $id)
            ->orWhere('credit_account_id', $id)
            ->exists();
    }

    /**
     * コード順に並べる。コードが無いものは末尾へ。
     *
     * 区分での並べ替えはドメイン側 (TrialBalanceBuilder) が enum の
     * sortOrder() で行う。SQL では区分の文字列順にしかできず、
     * 「資産 → 負債 → …」の順にならないため。
     *
     * @param \Illuminate\Database\Eloquent\Builder<AccountModel> $query
     * @return \Illuminate\Support\Collection<int, AccountModel>
     */
    private function ordered($query)
    {
        return $query->orderByRaw('code IS NULL')->orderBy('code')->orderBy('id')->get();
    }
}
