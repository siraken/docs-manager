<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Mapper\CustomerMapper;
use App\Infrastructure\Persistence\Eloquent\Models\Customer as CustomerModel;

final class CustomerRepository implements CustomerRepositoryInterface
{
    /** @return list<Customer> */
    public function listAll(): array
    {
        return CustomerModel::orderBy('id')->get()
            ->map(CustomerMapper::toDomain(...))
            ->all();
    }

    public function findById(int $id): ?Customer
    {
        $model = CustomerModel::find($id);

        return $model === null ? null : CustomerMapper::toDomain($model);
    }

    public function save(Customer $customer): Customer
    {
        $model = $customer->id() === null
            ? new CustomerModel()
            : CustomerModel::find($customer->id()) ?? new CustomerModel();

        $model->fill(CustomerMapper::toAttributes($customer));
        $model->save();

        $customer->assignId((int) $model->id);

        return $customer;
    }
}
