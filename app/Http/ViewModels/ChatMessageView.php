<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Chat\Entity\ChatMessage;
use Illuminate\Support\Collection;

final readonly class ChatMessageView implements \JsonSerializable
{
    private function __construct(
        public ?int $id,
        public int $userId,
        public string $userName,
        public string $body,
        public string $postedAt,
        public string $postedAtLabel,
        /** 自分の発言か。画面は見た目を変え、削除ボタンを出し分ける */
        public bool $isMine,
    ) {
    }

    /**
     * @param array<int, string> $userNames ユーザー ID => 名前
     * @param int|null $currentUserId ログイン中のユーザー
     */
    public static function fromEntity(ChatMessage $message, array $userNames = [], ?int $currentUserId = null): self
    {
        $postedAt = $message->postedAt();

        return new self(
            id: $message->id(),
            userId: $message->userId(),
            userName: $userNames[$message->userId()] ?? '(削除されたユーザー)',
            body: $message->body(),
            // 参考実装は日時を出す処理がコメントアウトされたままで、
            // 誰がいつ発言したのか画面から分からなかった
            postedAt: $postedAt?->format('Y-m-d H:i') ?? '',
            postedAtLabel: $postedAt?->format('n/j H:i') ?? '',
            isMine: $currentUserId !== null && $message->userId() === $currentUserId,
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
            'userId' => $this->userId,
            'userName' => $this->userName,
            'body' => $this->body,
            'postedAt' => $this->postedAt,
            'postedAtLabel' => $this->postedAtLabel,
            'isMine' => $this->isMine,

            // 消せるのは自分の発言だけ
            'urls' => $this->id === null || !$this->isMine ? null : [
                'delete' => route('chat.delete', ['id' => $this->id]),
            ],
        ];
    }

    /**
     * @param list<ChatMessage> $messages
     * @param array<int, string> $userNames
     * @return Collection<int, self>
     */
    public static function collection(array $messages, array $userNames = [], ?int $currentUserId = null): Collection
    {
        return collect($messages)
            ->map(static fn (ChatMessage $m): self => self::fromEntity($m, $userNames, $currentUserId))
            ->values();
    }
}
