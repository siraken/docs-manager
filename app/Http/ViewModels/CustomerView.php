<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Customer\Entity\Customer;
use Illuminate\Support\Collection;

final readonly class CustomerView
{
    private function __construct(
        public ?int $id,
        public string $name,
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

    /** 新規作成フォーム用の空の入れ物 */
    public static function empty(): self
    {
        return new self(null, '', false, null, null, null, null, null, null, null, null, '');
    }

    /**
     * @param list<Customer> $customers
     * @return Collection<int, self>
     */
    public static function collection(array $customers): Collection
    {
        return collect($customers)->map(self::fromEntity(...))->values();
    }

    /** フォームのテキスト項目を name => value で引くための対応表 */
    public function formValue(string $field): ?string
    {
        return match ($field) {
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'post_code' => $this->postCode,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'note' => $this->note,
            default => null,
        };
    }
}
