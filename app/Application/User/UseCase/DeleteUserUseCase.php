<?php

declare(strict_types=1);

namespace App\Application\User\UseCase;

use App\Application\User\Exception\CannotDeleteLastUserException;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;

/**
 * ユーザーの削除。
 *
 * 一覧に削除ボタンはあったが、押すと未定義の JS 関数 (deleteItem) を呼ぶだけで
 * サーバー側の受け口も無かった。
 *
 * 最後の 1 人を消すと誰もログインできなくなるため、それは拒否する
 * (ログイン画面はユーザーが 0 人のとき作成画面へ誘導するので復旧はできるが、
 * その間は認証なしでユーザーを作れてしまうため塞いでおく)。
 */
final readonly class DeleteUserUseCase
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    public function execute(int $id): void
    {
        $user = $this->users->findById($id)
            ?? throw EntityNotFoundException::of('ユーザー', $id);

        if ($this->users->count() <= 1) {
            throw new CannotDeleteLastUserException();
        }

        $this->users->delete((int) $user->id());
    }
}
