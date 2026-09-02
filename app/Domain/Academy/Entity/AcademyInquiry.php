<?php

declare(strict_types=1);

namespace App\Domain\Academy\Entity;

use App\Domain\User\ValueObject\EmailAddress;

/**
 * Lumo Academy の問い合わせ (lumo_users テーブル)。
 *
 * 移行前の AcademyController::register() は fill() を呼ぶだけで save() しておらず、
 * 保存されないまま 200 を返していた。問い合わせは失うと取り返せないので保存する。
 */
final class AcademyInquiry
{
    private function __construct(
        private ?int $id,
        private readonly string $name,
        private readonly EmailAddress $email,
        private readonly string $inquiry,
    ) {
    }

    public static function create(string $name, EmailAddress $email, string $inquiry): self
    {
        return new self(null, $name, $email, $inquiry);
    }

    public static function reconstitute(int $id, string $name, EmailAddress $email, string $inquiry): self
    {
        return new self($id, $name, $email, $inquiry);
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

    public function email(): EmailAddress
    {
        return $this->email;
    }

    public function inquiry(): string
    {
        return $this->inquiry;
    }
}
