<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Mapper\OrderMapper;
use App\Infrastructure\Persistence\Eloquent\Models\OrderDetail;
use App\Infrastructure\Persistence\Eloquent\Models\OrderHeader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class OrderRepository implements OrderRepositoryInterface
{
    /** @return list<Order> */
    public function listActive(): array
    {
        // is_deleted が NULL の行も「削除されていない」とみなす。
        // 移行前の where('is_deleted', '!=', 1) は SQL の NULL 比較の都合で
        // NULL 行を落としていた。
        return $this->listBy(
            static fn (Builder $query): Builder => $query->where(
                static fn (Builder $q): Builder => $q->whereNull('is_deleted')->orWhere('is_deleted', '!=', 1),
            ),
        );
    }

    /** @return list<Order> */
    public function listTrashed(): array
    {
        return $this->listBy(static fn (Builder $query): Builder => $query->where('is_deleted', 1));
    }

    public function findById(int $id): ?Order
    {
        $header = OrderHeader::find($id);

        if ($header === null) {
            return null;
        }

        return OrderMapper::toDomain($header, $this->detailsFor([$id])[$id] ?? []);
    }

    public function save(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $model = $order->id() === null
                ? new OrderHeader()
                : OrderHeader::find($order->id()) ?? new OrderHeader();

            $model->fill(OrderMapper::toHeaderAttributes($order));
            $model->save();

            $orderId = (int) $model->id;
            $order->assignId($orderId);

            // 明細は洗い替え。行の増減と並び替えが同時に起きるため、
            // 差分更新にせず全消し + 再作成にしている。
            OrderDetail::where('slip_id', $orderId)->delete();

            foreach ($order->lines() as $line) {
                OrderDetail::create(OrderMapper::toDetailAttributes($orderId, $line));
            }

            return $order;
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(static function () use ($id): void {
            OrderDetail::where('slip_id', $id)->delete();
            OrderHeader::where('id', $id)->delete();
        });
    }

    /**
     * @param \Closure(Builder): Builder $filter
     * @return list<Order>
     */
    private function listBy(\Closure $filter): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, OrderHeader> $headers */
        $headers = $filter(OrderHeader::query())->orderBy('id')->get();

        if ($headers->isEmpty()) {
            return [];
        }

        // 明細は 1 クエリでまとめて引く (ヘッダー 1 件ごとに引くと N+1 になる)
        $detailsByOrderId = $this->detailsFor($headers->pluck('id')->all());

        $orders = [];

        foreach ($headers as $header) {
            $orders[] = OrderMapper::toDomain($header, $detailsByOrderId[(int) $header->id] ?? []);
        }

        return $orders;
    }

    /**
     * @param list<int|string> $orderIds
     * @return array<int, list<OrderDetail>>
     */
    private function detailsFor(array $orderIds): array
    {
        $grouped = [];

        foreach (OrderDetail::whereIn('slip_id', $orderIds)->orderBy('id')->get() as $detail) {
            $grouped[(int) $detail->slip_id][] = $detail;
        }

        return $grouped;
    }
}
