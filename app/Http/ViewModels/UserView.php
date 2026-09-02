<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\User\Entity\User;
use Illuminate\Support\Collection;

final readonly class UserView
{
    private function __construct(
        public ?int $id,
        public string $name,
        public string $email,
        public ?string $nfcSerialNumber,
        public ?string $walletAddress,
        public bool $hasTwoFactor,
        public string $updatedAt,
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->id(),
            name: $user->name(),
            email: (string) $user->email(),
            nfcSerialNumber: $user->nfcCredential()?->serialNumber,
            walletAddress: (string) $user->walletAddress() === '' ? null : (string) $user->walletAddress(),
            hasTwoFactor: $user->hasTwoFactorEnabled(),
            updatedAt: $user->updatedAt()?->format('Y-m-d H:i') ?? '-',
        );
    }

    /** 新規作成フォーム用の空の入れ物 */
    public static function empty(): self
    {
        return new self(null, '', '', null, null, false, '-');
    }

    /**
     * @param list<User> $users
     * @return Collection<int, self>
     */
    public static function collection(array $users): Collection
    {
        return collect($users)->map(self::fromEntity(...))->values();
    }
}
