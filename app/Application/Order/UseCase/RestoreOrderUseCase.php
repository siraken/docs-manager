<?php

declare(strict_types=1);

namespace App\Application\Order\UseCase;

use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

/** ごみ箱から戻す (is_deleted = 0) */
final readonly class RestoreOrderUseCase
{
    public function __construct(private OrderRepositoryInterface $orders)
    {
    }

    public function execute(int $id): void
    {
        $order = $this->orders->findById($id)
            ?? throw EntityNotFoundException::of('発注書', $id);

        $order->restore();

        $this->orders->save($order);
    }
}
