<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\EmailAddress;

interface UserRepositoryInterface
{
    /** @return list<User> */
    public function listAll(): array;

    public function findById(int $id): ?User;

    public function findByEmail(EmailAddress $email): ?User;

    /** NFC のシリアル番号で引く。PIN の照合はエンティティ側で行う */
    public function findByNfcSerialNumber(string $serialNumber): ?User;

    public function findByWalletAddress(string $address): ?User;

    public function count(): int;

    public function save(User $user): User;

    public function delete(int $id): void;
}
