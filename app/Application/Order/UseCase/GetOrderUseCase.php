<?php

declare(strict_types=1);

namespace App\Application\Order\UseCase;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

/** 発注書を 1 件取得する。無ければ例外 (HTTP 層が 404 に変換する) */
final readonly class GetOrderUseCase
{
    public function __construct(private OrderRepositoryInterface $orders)
    {
    }

    public function execute(int $id): Order
    {
        return $this->orders->findById($id)
            ?? throw EntityNotFoundException::of('発注書', $id);
    }
}
