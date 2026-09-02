<?php

declare(strict_types=1);

namespace App\Application\Customer\UseCase;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class GetCustomerUseCase
{
    public function __construct(private CustomerRepositoryInterface $customers)
    {
    }

    public function execute(int $id): Customer
    {
        return $this->customers->findById($id)
            ?? throw EntityNotFoundException::of('顧客', $id);
    }
}
