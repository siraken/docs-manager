<?php

declare(strict_types=1);

namespace App\Application\Order\UseCase;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepositoryInterface;

/** 発注書一覧 (ごみ箱を除く) */
final readonly class ListOrdersUseCase
{
    public function __construct(private OrderRepositoryInterface $orders)
    {
    }

    /** @return list<Order> */
    public function execute(): array
    {
        return $this->orders->listActive();
    }
}
