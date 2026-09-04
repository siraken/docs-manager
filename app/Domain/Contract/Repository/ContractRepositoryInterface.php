<?php

declare(strict_types=1);

namespace App\Domain\Contract\Repository;

use App\Domain\Contract\Entity\Contract;

interface ContractRepositoryInterface
{
    /** @return list<Contract> */
    public function listAll(): array;

    public function findById(int $id): ?Contract;

    public function save(Contract $contract): Contract;

    public function delete(int $id): void;
}
