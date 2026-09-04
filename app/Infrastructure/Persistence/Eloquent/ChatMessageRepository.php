<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Chat\Entity\ChatMessage;
use App\Domain\Chat\Repository\ChatMessageRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Mapper\ChatMessageMapper;
use App\Infrastructure\Persistence\Eloquent\Models\ChatMessage as ChatMessageModel;

final class ChatMessageRepository implements ChatMessageRepositoryInterface
{
    /** @return list<ChatMessage> 新しい順 */
    public function listLatest(int $limit): array
    {
        // created_at だけだと同じ秒に入った発言の順が定まらないので id も見る
        return ChatMessageModel::orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(ChatMessageMapper::toDomain(...))
            ->all();
    }

    public function findById(int $id): ?ChatMessage
    {
        $model = ChatMessageModel::find($id);

        return $model === null ? null : ChatMessageMapper::toDomain($model);
    }

    public function save(ChatMessage $message): ChatMessage
    {
        $model = $message->id() === null
            ? new ChatMessageModel()
            : ChatMessageModel::find($message->id()) ?? new ChatMessageModel();

        $model->fill(ChatMessageMapper::toAttributes($message));
        $model->save();

        $message->assignId((int) $model->id);

        return $message;
    }

    public function delete(int $id): void
    {
        ChatMessageModel::destroy($id);
    }
}
