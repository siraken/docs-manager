<?php

declare(strict_types=1);

namespace App\Application\Customer\UseCase;

use App\Application\Customer\Input\CustomerInput;
use App\Domain\Customer\Entity\Customer;
use App\Domain\Customer\Repository\CustomerRepositoryInterface;

final readonly class CreateCustomerUseCase
{
    public function __construct(private CustomerRepositoryInterface $customers)
    {
    }

    public function execute(CustomerInput $input): Customer
    {
        $customer = Customer::create(
            name: $input->name,
            isCompany: $input->isCompany,
            email: $input->email,
            phone: $input->phone,
            postCode: $input->postCode,
            address: $input->address,
            city: $input->city,
            state: $input->state,
            country: $input->country,
            note: $input->note,
        );

        return $this->customers->save($customer);
    }
}
