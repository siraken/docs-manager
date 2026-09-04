<?php

declare(strict_types=1);

namespace App\Application\Contract\UseCase;

use App\Application\Contract\Input\ContractInput;
use App\Domain\Contract\Entity\Contract;
use App\Domain\Contract\Repository\ContractRepositoryInterface;

final readonly class CreateContractUseCase
{
    public function __construct(private ContractRepositoryInterface $contracts)
    {
    }

    public function execute(ContractInput $input): Contract
    {
        $contract = Contract::create(
            name: $input->name,
            contractNo: $input->contractNo,
            customerId: $input->customerId,
            term: $input->termValue(),
            description: $input->description,
        );

        return $this->contracts->save($contract);
    }
}
