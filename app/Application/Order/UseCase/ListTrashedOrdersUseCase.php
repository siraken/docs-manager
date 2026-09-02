<?php

declare(strict_types=1);

namespace App\Application\Order\UseCase;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepositoryInterface;

/** ごみ箱の発注書一覧 */
final readonly class ListTrashedOrdersUseCase
{
    public function __construct(private OrderRepositoryInterface $orders)
    {
    }

    /** @return list<Order> */
    public function execute(): array
    {
        return $this->orders->listTrashed();
    }
}
