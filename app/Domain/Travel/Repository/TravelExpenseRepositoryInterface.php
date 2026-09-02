<?php

declare(strict_types=1);

namespace App\Domain\Travel\Repository;

use App\Domain\Travel\Entity\TravelExpense;

interface TravelExpenseRepositoryInterface
{
    /** @return list<TravelExpense> */
    public function listAll(): array;

    public function findById(int $id): ?TravelExpense;

    public function save(TravelExpense $expense): TravelExpense;

    /**
     * まとめて登録する (CSV 取り込み用)。
     *
     * @param list<TravelExpense> $expenses
     * @return int 登録件数
     */
    public function saveAll(array $expenses): int;
}
