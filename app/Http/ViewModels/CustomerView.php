<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Customer\Entity\Customer;
use Illuminate\Support\Collection;

final readonly class CustomerView implements \JsonSerializable
{
    private function __construct(
        public ?int $id,
        public string $name,
        public ?string $person,
        public bool $isCompany,
        public ?string $email,
        public ?string $phone,
        public ?string $postCode,
        public ?string $address,
        public ?string $city,
        public ?string $state,
        public ?string $country,
        public ?string $note,
        public string $location,
    ) {
    }

    public static function fromEntity(Customer $customer): self
    {
        return new self(
            id: $customer->id(),
            name: $customer->name(),
            person: $customer->person(),
            isCompany: $customer->isCompany(),
            email: $customer->email(),
            phone: $customer->phone(),
            postCode: $customer->postCode(),
            address: $customer->address(),
            city: $customer->city(),
            state: $customer->state(),
            country: $customer->country(),
            note: $customer->note(),
            location: $customer->locationLabel(),
        );
    }

    /**
     * Inertia の props 用。フォームの初期値に使うので全項目を出す。
     *
     * 発注書フォームの取引先セレクトはこれではなく options() を使う
     * (セレクトに要るのは id と名前だけで、全顧客の住所まで送る必要は無い)。
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'person' => $this->person,
            'isCompany' => $this->isCompany,
            'email' => $this->email,
            'phone' => $this->phone,
            'postCode' => $this->postCode,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'note' => $this->note,
            'location' => $this->location,

            'urls' => $this->id === null ? null : [
                'edit' => route('customers.edit', ['id' => $this->id]),
            ],
        ];
    }

    /** 新規作成フォーム用の空の入れ物 */
    public static function empty(): self
    {
        return new self(null, '', null, false, null, null, null, null, null, null, null, null, '');
    }

    /**
     * @param list<Customer> $customers
     * @return Collection<int, self>
     */
    public static function collection(array $customers): Collection
    {
        return collect($customers)->map(self::fromEntity(...))->values();
    }

    /**
     * セレクトの選択肢。発注書フォームが使う。
     *
     * @param list<Customer> $customers
     * @return list<array{id: int|null, name: string, person: string|null}>
     */
    public static function options(array $customers): array
    {
        return array_map(
            static fn (Customer $customer): array => [
                'id' => $customer->id(),
                'name' => $customer->name(),
                // 発注書フォームが担当者欄の初期値に使う。契約・勤務報告の
                // セレクトでは読まないが、短い文字列 1 つなので options() を
                // 分けるより 1 本にまとめておく
                'person' => $customer->person(),
            ],
            $customers,
        );
    }
}
