<?php

declare(strict_types=1);

namespace App\Application\Order\UseCase;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Order\ValueObject\StatusKind;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * 一覧のステータスピルを押したときの遷移。
 *
 * 移行前は現在値をクライアントから受け取り (currentStatus)、それを基に次の値を
 * 決めていたため、画面が古いと保存結果もずれた。ここでは保存済みの値から
 * 次の状態を決めるので、クライアントの申告に依存しない。
 */
final readonly class ChangeOrderStatusUseCase
{
    public function __construct(private OrderRepositoryInterface $orders)
    {
    }

    public function execute(int $id, StatusKind $kind): Order
    {
        $order = $this->orders->findById($id)
            ?? throw EntityNotFoundException::of('発注書', $id);

        $order->advanceStatus($kind);

        return $this->orders->save($order);
    }
}
