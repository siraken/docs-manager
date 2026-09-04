<?php

declare(strict_types=1);

namespace App\Domain\Chat\Repository;

use App\Domain\Chat\Entity\ChatMessage;

interface ChatMessageRepositoryInterface
{
    /**
     * 新しい順に取り出す。画面では古い順に並べ替えて表示する。
     *
     * @return list<ChatMessage>
     */
    public function listLatest(int $limit): array;

    public function findById(int $id): ?ChatMessage;

    public function save(ChatMessage $message): ChatMessage;

    public function delete(int $id): void;
}
