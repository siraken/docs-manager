<?php

declare(strict_types=1);

namespace App\Application\Chat\UseCase;

use App\Application\Auth\Port\AuthSessionInterface;
use App\Domain\Chat\Entity\ChatMessage;
use App\Domain\Chat\Repository\ChatMessageRepositoryInterface;
use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 発言を投稿する。
 *
 * **投稿者はクライアントから受け取らない。** ログイン中のユーザーを
 * セッションから取る (参考実装も session('id') を使っていた)。
 * 画面から user_id を送らせると、他人になりすまして投稿できてしまう。
 */
final readonly class PostChatMessageUseCase
{
    public function __construct(
        private ChatMessageRepositoryInterface $messages,
        private AuthSessionInterface $session,
    ) {
    }

    public function execute(string $body): ChatMessage
    {
        $userId = $this->session->currentUserId()
            ?? throw new InvalidValueException('ログインし直してください。');

        return $this->messages->save(ChatMessage::post($userId, $body));
    }
}
