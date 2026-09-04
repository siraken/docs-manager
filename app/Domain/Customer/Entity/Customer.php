<?php

declare(strict_types=1);

namespace App\Domain\Customer\Entity;

/**
 * 顧客 (取引先)。発注書の宛先として参照される。
 */
final class Customer
{
    private function __construct(
        private ?int $id,
        private string $name,
        /** 取引先の担当者名。発注書フォームの担当者欄の初期値に使う */
        private ?string $person,
        private bool $isCompany,
        private ?string $email,
        private ?string $phone,
        private ?string $postCode,
        private ?string $address,
        private ?string $city,
        private ?string $state,
        private ?string $country,
        private ?string $note,
    ) {
    }

    public static function create(
        string $name,
        ?string $person,
        bool $isCompany,
        ?string $email = null,
        ?string $phone = null,
        ?string $postCode = null,
        ?string $address = null,
        ?string $city = null,
        ?string $state = null,
        ?string $country = null,
        ?string $note = null,
    ): self {
        return new self(null, $name, $person, $isCompany, $email, $phone, $postCode, $address, $city, $state, $country, $note);
    }

    public static function reconstitute(
        int $id,
        string $name,
        ?string $person,
        bool $isCompany,
        ?string $email,
        ?string $phone,
        ?string $postCode,
        ?string $address,
        ?string $city,
        ?string $state,
        ?string $country,
        ?string $note,
    ): self {
        return new self($id, $name, $person, $isCompany, $email, $phone, $postCode, $address, $city, $state, $country, $note);
    }

    public function update(
        string $name,
        ?string $person,
        bool $isCompany,
        ?string $email,
        ?string $phone,
        ?string $postCode,
        ?string $address,
        ?string $city,
        ?string $state,
        ?string $country,
        ?string $note,
    ): void {
        $this->name = $name;
        $this->person = $person;
        $this->isCompany = $isCompany;
        $this->email = $email;
        $this->phone = $phone;
        $this->postCode = $postCode;
        $this->address = $address;
        $this->city = $city;
        $this->state = $state;
        $this->country = $country;
        $this->note = $note;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function person(): ?string
    {
        return $this->person;
    }

    public function isCompany(): bool
    {
        return $this->isCompany;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function postCode(): ?string
    {
        return $this->postCode;
    }

    public function address(): ?string
    {
        return $this->address;
    }

    public function city(): ?string
    {
        return $this->city;
    }

    public function state(): ?string
    {
        return $this->state;
    }

    public function country(): ?string
    {
        return $this->country;
    }

    public function note(): ?string
    {
        return $this->note;
    }

    /** 一覧に出す所在地。市区町村・都道府県・国をそのまま連結する */
    public function locationLabel(): string
    {
        return $this->city . $this->state . $this->country;
    }
}
