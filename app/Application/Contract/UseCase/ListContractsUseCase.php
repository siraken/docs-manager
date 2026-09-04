<?php

declare(strict_types=1);

namespace App\Application\Contract\UseCase;

use App\Domain\Contract\Entity\Contract;
use App\Domain\Contract\Repository\ContractRepositoryInterface;

final readonly class ListContractsUseCase
{
    public function __construct(private ContractRepositoryInterface $contracts)
    {
    }

    /** @return list<Contract> */
    public function execute(): array
    {
        return $this->contracts->listAll();
    }
}
