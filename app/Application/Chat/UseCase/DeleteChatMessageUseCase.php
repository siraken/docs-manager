<?php

declare(strict_types=1);

namespace App\Application\Chat\UseCase;

use App\Application\Auth\Port\AuthSessionInterface;
use App\Application\Chat\Exception\CannotDeleteOthersMessageException;
use App\Domain\Chat\Repository\ChatMessageRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 発言を削除する。消せるのは自分の発言だけ。
 */
final readonly class DeleteChatMessageUseCase
{
    public function __construct(
        private ChatMessageRepositoryInterface $messages,
        private AuthSessionInterface $session,
    ) {
    }

    public function execute(int $id): void
    {
        $message = $this->messages->findById($id)
            ?? throw EntityNotFoundException::of('発言', $id);

        $userId = $this->session->currentUserId()
            ?? throw new InvalidValueException('ログインし直してください。');

        if (!$message->isDeletableBy($userId)) {
            throw CannotDeleteOthersMessageException::create();
        }

        $this->messages->delete($id);
    }
}
