<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\User\Entity\User;
use Illuminate\Support\Collection;

final readonly class UserView implements \JsonSerializable
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

    /**
     * Inertia の props 用。
     *
     * 未保存のユーザー (id が null) には編集や 2FA の URL が作れない。
     * 移行前のビューはこれを無条件に組み立てて新規登録画面を 500 にしていたので、
     * ここで null を返し、画面側は urls の有無でボタンを出し分ける。
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'nfcSerialNumber' => $this->nfcSerialNumber,
            'walletAddress' => $this->walletAddress,
            'hasTwoFactor' => $this->hasTwoFactor,
            'updatedAt' => $this->updatedAt,

            'urls' => $this->id === null ? null : [
                'edit' => route('users.edit', ['id' => $this->id]),
                'twoFactor' => route('users.2fa', ['id' => $this->id]),
                'delete' => route('users.delete', ['id' => $this->id]),
            ],
        ];
    }

    /** 新規作成フォーム用の空の入れ物 */
    public static function empty(): self
    {
        return new self(null, '', '', null, null, false, '-');
    }

    /**
     * セレクトの選択肢。勤務報告の担当者セレクトが使う。
     *
     * 一覧に要らない項目まで props に載せないため、id と名前だけを出す
     * (CustomerView::options() と同じ考え方)。
     *
     * @param list<User> $users
     * @return list<array{id: int|null, name: string}>
     */
    public static function options(array $users): array
    {
        return array_map(
            static fn (User $user): array => [
                'id' => $user->id(),
                'name' => $user->name(),
            ],
            $users,
        );
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
