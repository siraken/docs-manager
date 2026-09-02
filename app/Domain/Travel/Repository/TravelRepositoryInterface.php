<?php

declare(strict_types=1);

namespace App\Domain\Travel\Repository;

use App\Domain\Travel\Entity\Travel;

interface TravelRepositoryInterface
{
    /** @return list<Travel> */
    public function listAll(): array;

    public function findById(int $id): ?Travel;

    public function save(Travel $travel): Travel;

    /**
     * まとめて登録する (CSV 取り込み用)。
     *
     * @param list<Travel> $travels
     * @return int 登録件数
     */
    public function saveAll(array $travels): int;
}
