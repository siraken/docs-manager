<?php

declare(strict_types=1);

namespace App\Application\Order\UseCase;

use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

/** ごみ箱に入れる (is_deleted = 1)。行は残る */
final readonly class TrashOrderUseCase
{
    public function __construct(private OrderRepositoryInterface $orders)
    {
    }

    public function execute(int $id): void
    {
        $order = $this->orders->findById($id)
            ?? throw EntityNotFoundException::of('発注書', $id);

        $order->trash();

        $this->orders->save($order);
    }
}
