<?php

declare(strict_types=1);

namespace App\Application\Customer\UseCase;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Customer\Repository\CustomerRepositoryInterface;

final readonly class ListCustomersUseCase
{
    public function __construct(private CustomerRepositoryInterface $customers)
    {
    }

    /** @return list<Customer> */
    public function execute(): array
    {
        return $this->customers->listAll();
    }
}
