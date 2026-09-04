<?php

declare(strict_types=1);

namespace App\Application\Contract\UseCase;

use App\Application\Contract\Input\ContractInput;
use App\Domain\Contract\Entity\Contract;
use App\Domain\Contract\Repository\ContractRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class UpdateContractUseCase
{
    public function __construct(private ContractRepositoryInterface $contracts)
    {
    }

    public function execute(int $id, ContractInput $input): Contract
    {
        $contract = $this->contracts->findById($id)
            ?? throw EntityNotFoundException::of('契約', $id);

        $contract->update(
            name: $input->name,
            contractNo: $input->contractNo,
            customerId: $input->customerId,
            term: $input->termValue(),
            description: $input->description,
        );

        return $this->contracts->save($contract);
    }
}
