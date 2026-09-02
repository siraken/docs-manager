<?php

declare(strict_types=1);

namespace App\Domain\Customer\Repository;

use App\Domain\Customer\Entity\Customer;

interface CustomerRepositoryInterface
{
    /** @return list<Customer> */
    public function listAll(): array;

    public function findById(int $id): ?Customer;

    public function save(Customer $customer): Customer;
}
