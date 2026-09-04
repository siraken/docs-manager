<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mapper;

use App\Domain\Chat\Entity\ChatMessage as ChatMessageEntity;
use App\Infrastructure\Persistence\Eloquent\Models\ChatMessage as ChatMessageModel;

final class ChatMessageMapper
{
    public static function toDomain(ChatMessageModel $model): ChatMessageEntity
    {
        return ChatMessageEntity::reconstitute(
            id: (int) $model->id,
            userId: (int) $model->user_id,
            body: (string) $model->body,
            // created_at は Eloquent が Carbon で返す
            postedAt: $model->created_at === null
                ? null
                : \DateTimeImmutable::createFromInterface($model->created_at),
        );
    }

    /** @return array<string, mixed> */
    public static function toAttributes(ChatMessageEntity $message): array
    {
        return [
            'user_id' => $message->userId(),
            'body' => $message->body(),
        ];
    }
}
