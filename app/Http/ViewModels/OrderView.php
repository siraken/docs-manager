<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Order\Entity\Order;
use Illuminate\Support\Collection;

/**
 * 発注書をビューに渡すための形。
 *
 * ドメインエンティティを Blade へ直接渡すと、表示のための整形 (日付書式・
 * 桁区切り・ラベル) がテンプレートに散る。ここで整形済みの値にしておく。
 */
final readonly class OrderView implements \JsonSerializable
{
    /** @param list<OrderLineView> $lines */
    private function __construct(
        public int $id,
        public string $orderNo,
        public ?string $title,
        public int $customerId,
        public string $customerName,
        public ?string $responsible,
        public ?string $honorTitle,
        public string $issuedDate,
        public ?string $expDate,
        public string $issuedDateLabel,
        public string $expDateLabel,
        public int $subtotal,
        public int $tax,
        public int $total,
        public string $totalLabel,
        public ?string $remarks,
        public int $issueStatus,
        public int $orderStatus,
        public bool $isDeleted,
        public ?string $note,
        public array $lines,
    ) {
    }

    public static function fromEntity(Order $order, string $customerName = ''): self
    {
        return new self(
            id: (int) $order->id(),
            orderNo: (string) $order->orderNo(),
            title: $order->title(),
            customerId: $order->customerId(),
            customerName: $customerName,
            responsible: $order->responsible(),
            honorTitle: $order->honorTitle(),
            issuedDate: $order->issuedDate()->format('Y-m-d'),
            expDate: $order->expDate()?->format('Y-m-d'),
            issuedDateLabel: $order->issuedDate()->format('Y/m/d'),
            expDateLabel: $order->expDate()?->format('Y/m/d') ?? '-',
            subtotal: $order->subtotal()->amount,
            tax: $order->tax()->amount,
            total: $order->total()->amount,
            totalLabel: $order->total()->format(),
            remarks: $order->remarks(),
            issueStatus: $order->issueStatus()->value,
            orderStatus: $order->orderStatus()->value,
            isDeleted: $order->isDeleted(),
            note: $order->note(),
            lines: array_map(OrderLineView::fromEntity(...), $order->lines()),
        );
    }

    /**
     * @param list<Order> $orders
     * @param array<int, string> $customerNames 顧客 ID => 顧客名
     * @return Collection<int, self>
     */
    public static function collection(array $orders, array $customerNames = []): Collection
    {
        return collect($orders)->map(
            static fn (Order $order): self => self::fromEntity($order, $customerNames[$order->customerId()] ?? ''),
        )->values();
    }

    /**
     * Inertia は props を JSON にして Svelte へ渡す。メソッド (displayName) の
     * 結果や、各行から辿るリンクの URL もここで確定させる。
     *
     * 画面から参照する URL をサーバー側で組むのは Inertia でよくあるやり方で、
     * Ziggy のようなルートヘルパをフロントに持ち込まずに済む。
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'orderNo' => $this->orderNo,
            'title' => $this->title,
            'customerId' => $this->customerId,
            'customerName' => $this->customerName,
            'responsible' => $this->responsible,
            'honorTitle' => $this->honorTitle,
            'issuedDate' => $this->issuedDate,
            'expDate' => $this->expDate,
            'issuedDateLabel' => $this->issuedDateLabel,
            'expDateLabel' => $this->expDateLabel,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'total' => $this->total,
            'totalLabel' => $this->totalLabel,
            'remarks' => $this->remarks,
            'issueStatus' => $this->issueStatus,
            'orderStatus' => $this->orderStatus,
            'isDeleted' => $this->isDeleted,
            'note' => $this->note,
            'lines' => $this->lines,

            'displayName' => $this->displayName(),
            'addressee' => $this->addressee(),

            'urls' => [
                'show' => route('orders.view', ['id' => $this->id]),
                'edit' => route('orders.edit', ['id' => $this->id]),
                'pdf' => route('orders.pdf', ['id' => $this->id]),
                'csv' => route('orders.csv', ['id' => $this->id]),
                'trash' => route('orders.delete', ['id' => $this->id]),
                'restore' => route('orders.restore', ['id' => $this->id]),
            ],
        ];
    }

    /** 宛名。「顧客名 担当者 御中」の形に組む */
    public function addressee(): string
    {
        return trim(implode(' ', array_filter([
            $this->customerName,
            $this->responsible,
            $this->honorTitle,
        ], static fn (?string $part): bool => $part !== null && $part !== '')));
    }

    /** 一覧の見出しに出す文字列。件名が無ければ顧客名で代替する */
    public function displayName(): string
    {
        if ($this->title !== null && $this->title !== '') {
            return $this->title;
        }

        return $this->customerName !== '' ? $this->customerName : '#' . $this->orderNo;
    }
}
