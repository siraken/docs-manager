<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Application\Auth\Port\AuthSessionInterface;
use App\Domain\User\Entity\User;
use Illuminate\Session\Store;

/**
 * ログイン状態をセッションに置く実装。
 *
 * このアプリは Illuminate\Auth を使わず session('user_id'/'name'/'email') を
 * 手で組み立てている。LoginMiddleware が session('name') の有無を見ているため、
 * キーの構成は変えていない。
 *
 * config/session.php の serialization は json なので、ここに入れてよいのは
 * スカラー値だけ (オブジェクトを入れると復元できない)。
 */
final readonly class SessionAuthStore implements AuthSessionInterface
{
    // regenerate() はセッションのコントラクトではなく具象 Store にしかないため、
    // ここでは Store を受け取る (Infrastructure 層なのでフレームワーク依存でよい)
    public function __construct(private Store $session)
    {
    }

    public function login(User $user): void
    {
        // セッション固定攻撃を避けるため、ログイン時に ID を振り直す
        $this->session->regenerate();

        $this->session->put([
            'user_id' => $user->id(),
            'name' => $user->name(),
            'email' => (string) $user->email(),
        ]);
    }

    public function logout(): void
    {
        $this->session->flush();
    }

    public function currentUserId(): ?int
    {
        $id = $this->session->get('user_id');

        return $id === null ? null : (int) $id;
    }
}
