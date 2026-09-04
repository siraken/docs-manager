<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Contract\Entity\Contract;
use Illuminate\Support\Collection;

final readonly class ContractView implements \JsonSerializable
{
    private function __construct(
        public ?int $id,
        public string $name,
        public ?string $contractNo,
        public ?int $customerId,
        public string $customerName,
        public ?string $startDate,
        public ?string $endDate,
        public string $termLabel,
        public string $status,
        public string $statusValue,
        public ?string $description,
    ) {
    }

    /**
     * @param string $customerName 取引先名。一覧では N+1 を避けるため
     *                             コントローラがまとめて引いた対応表から渡す
     */
    public static function fromEntity(Contract $contract, string $customerName = ''): self
    {
        // 状態は「今日」を基準に導出する。保存されたカラムではないので
        // 日付が変われば表示も変わる
        $status = $contract->statusOn(new \DateTimeImmutable());

        return new self(
            id: $contract->id(),
            name: $contract->name(),
            contractNo: $contract->contractNo(),
            customerId: $contract->customerId(),
            customerName: $customerName,
            startDate: $contract->term()->startDate?->format('Y-m-d'),
            endDate: $contract->term()->endDate?->format('Y-m-d'),
            termLabel: $contract->term()->label(),
            status: $status->label(),
            statusValue: $status->value,
            description: $contract->description(),
        );
    }

    /**
     * Inertia の props 用。
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'contractNo' => $this->contractNo,
            'customerId' => $this->customerId,
            'customerName' => $this->customerName,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'termLabel' => $this->termLabel,
            'status' => $this->status,
            'statusValue' => $this->statusValue,
            'description' => $this->description,

            // 未保存の契約に route(..., ['id' => null]) は組めない
            'urls' => $this->id === null ? null : [
                'edit' => route('contracts.edit', ['id' => $this->id]),
                'delete' => route('contracts.delete', ['id' => $this->id]),
            ],
        ];
    }

    /**
     * @param list<Contract> $contracts
     * @param array<int, string> $customerNames 顧客 ID => 顧客名
     * @return Collection<int, self>
     */
    public static function collection(array $contracts, array $customerNames = []): Collection
    {
        return collect($contracts)
            ->map(static fn (Contract $contract): self => self::fromEntity(
                $contract,
                $customerNames[$contract->customerId()] ?? '',
            ))
            ->values();
    }
}
