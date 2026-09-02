<?php

declare(strict_types=1);

namespace App\Application\Auth\Port;

use App\Domain\User\Entity\User;

/**
 * ログイン状態の保管。
 *
 * このアプリは Illuminate\Auth を使わず session('user_id'/'name'/'email') を
 * 手で組み立てている。その置き場所をユースケースから隠すためのポート。
 */
interface AuthSessionInterface
{
    public function login(User $user): void;

    public function logout(): void;

    public function currentUserId(): ?int;
}
