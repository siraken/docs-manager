<?php

declare(strict_types=1);

namespace App\Domain\Accounting\Repository;

use App\Domain\Accounting\Entity\Account;

interface AccountRepositoryInterface
{
    /** @return list<Account> */
    public function listAll(): array;

    /**
     * 仕訳フォームの選択肢に出す科目。無効化したものは除く。
     *
     * @return list<Account>
     */
    public function listActive(): array;

    public function findById(int $id): ?Account;

    public function save(Account $account): Account;

    public function delete(int $id): void;

    /** その科目を参照している仕訳があるか。削除の可否判定に使う */
    public function isReferenced(int $id): bool;
}
