<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mapper;

use App\Domain\Customer\Entity\Customer as CustomerEntity;
use App\Infrastructure\Persistence\Eloquent\Models\Customer as CustomerModel;

final class CustomerMapper
{
    public static function toDomain(CustomerModel $model): CustomerEntity
    {
        return CustomerEntity::reconstitute(
            id: (int) $model->id,
            name: (string) $model->name,
            person: $model->person,
            isCompany: (int) $model->is_company === 1,
            email: $model->email,
            phone: $model->phone,
            postCode: $model->post_code,
            address: $model->address,
            city: $model->city,
            state: $model->state,
            country: $model->country,
            note: $model->note,
        );
    }

    /** @return array<string, mixed> */
    public static function toAttributes(CustomerEntity $customer): array
    {
        return [
            'name' => $customer->name(),
            'person' => $customer->person(),
            'is_company' => $customer->isCompany() ? 1 : 0,
            'email' => $customer->email(),
            'phone' => $customer->phone(),
            'post_code' => $customer->postCode(),
            'address' => $customer->address(),
            'city' => $customer->city(),
            'state' => $customer->state(),
            'country' => $customer->country(),
            'note' => $customer->note(),
        ];
    }
}
