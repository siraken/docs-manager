<?php

declare(strict_types=1);

namespace App\Application\Contract\UseCase;

use App\Domain\Contract\Entity\Contract;
use App\Domain\Contract\Repository\ContractRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class GetContractUseCase
{
    public function __construct(private ContractRepositoryInterface $contracts)
    {
    }

    public function execute(int $id): Contract
    {
        return $this->contracts->findById($id)
            ?? throw EntityNotFoundException::of('契約', $id);
    }
}
