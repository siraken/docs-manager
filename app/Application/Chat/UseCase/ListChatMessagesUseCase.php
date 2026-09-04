<?php

declare(strict_types=1);

namespace App\Application\Chat\UseCase;

use App\Domain\Chat\Entity\ChatMessage;
use App\Domain\Chat\Repository\ChatMessageRepositoryInterface;

/**
 * 直近の発言を古い順に返す。
 *
 * 参考実装は 15 件固定で、しかも新しい順のまま画面へ渡して JS 側で
 * reverse() していた。並べ替えはここで済ませる。
 */
final readonly class ListChatMessagesUseCase
{
    /** 一度に読み込む件数 */
    public const DEFAULT_LIMIT = 50;

    public function __construct(private ChatMessageRepositoryInterface $messages)
    {
    }

    /** @return list<ChatMessage> 古い順 */
    public function execute(int $limit = self::DEFAULT_LIMIT): array
    {
        return array_reverse($this->messages->listLatest($limit));
    }
}
