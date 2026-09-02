<?php

declare(strict_types=1);

namespace App\Application\Order\UseCase;

use App\Application\Order\Input\OrderInput;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepositoryInterface;

final readonly class CreateOrderUseCase
{
    public function __construct(private OrderRepositoryInterface $orders)
    {
    }

    public function execute(OrderInput $input): Order
    {
        $order = Order::create(
            customerId: $input->customerId,
            responsible: $input->responsible,
            honorTitle: $input->honorTitle,
            issuedDate: $input->issuedDateValue(),
            expDate: $input->expDateValue(),
            orderNo: $input->orderNoValue(),
            title: $input->title,
            remarks: $input->remarks,
            lines: $input->toOrderLines(),
        );

        return $this->orders->save($order);
    }
}
