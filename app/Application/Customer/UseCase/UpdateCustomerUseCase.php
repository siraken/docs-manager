<?php

declare(strict_types=1);

namespace App\Application\Customer\UseCase;

use App\Application\Customer\Input\CustomerInput;
use App\Domain\Customer\Entity\Customer;
use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * 顧客の更新。
 *
 * 移行前の CustomerController::edit() には POST 分岐が無く、保存ボタンを押しても
 * フォームを描き直すだけで何も起きなかった (画面上は成功したように見える)。
 */
final readonly class UpdateCustomerUseCase
{
    public function __construct(private CustomerRepositoryInterface $customers)
    {
    }

    public function execute(int $id, CustomerInput $input): Customer
    {
        $customer = $this->customers->findById($id)
            ?? throw EntityNotFoundException::of('顧客', $id);

        $customer->update(
            name: $input->name,
            person: $input->person,
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
