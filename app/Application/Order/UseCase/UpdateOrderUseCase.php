<?php

declare(strict_types=1);

namespace App\Application\Order\UseCase;

use App\Application\Order\Input\OrderInput;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * 発注書の更新。
 *
 * 移行前は「ヘッダーと明細を物理削除してから作り直す」実装で、更新のたびに
 * id が変わっていた。さらにフォームに存在しない is_issued 等を参照しており、
 * POST すると Undefined array key で 500 になって保存自体できなかった。
 *
 * ここでは集約の同一性を保ったまま更新し、発行・受注ステータスや
 * ごみ箱フラグ、社内メモといった「フォームが持たない項目」は現在値を維持する。
 */
final readonly class UpdateOrderUseCase
{
    public function __construct(private OrderRepositoryInterface $orders)
    {
    }

    public function execute(int $id, OrderInput $input): Order
    {
        $order = $this->orders->findById($id)
            ?? throw EntityNotFoundException::of('発注書', $id);

        $order->update(
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
