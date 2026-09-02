<?php

declare(strict_types=1);

namespace App\Infrastructure\Mail;

use App\Application\Auth\Port\LoginContext;
use App\Application\Auth\Port\LoginNotifierInterface;
use App\Domain\User\Entity\User;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\Log;

/**
 * ログイン通知メール。
 *
 * 本番環境でだけ送るのは移行前と同じ。判定を env() から config() に変えている
 * (config:cache 済みだと env() は null を返すため、キャッシュした本番環境で
 * 通知が止まる可能性があった)。
 *
 * 送信に失敗してもログインは成立させる。移行前は Mail::send() の例外がそのまま
 * 外へ出ており、メールサーバーが落ちているとログインできなかった。
 */
final readonly class MailLoginNotifier implements LoginNotifierInterface
{
    public function __construct(private Mailer $mailer)
    {
    }

    public function notifyLogin(User $user, LoginContext $context): void
    {
        if (config('app.env') !== 'production') {
            return;
        }

        try {
            $this->mailer->send(
                ['text' => 'emails.login'],
                [
                    'datetime' => $context->occurredAt->format('Y-m-d H:i:s'),
                    'name' => $user->name(),
                    'ip' => $context->ipAddress,
                    'user_agent' => $context->userAgent,
                ],
                function ($message) use ($user): void {
                    $message
                        ->from('system@novalumo.llc', 'Novalumo Docs Manager')
                        ->to((string) $user->email(), $user->name())
                        ->subject('ログイン通知');
                },
            );
        } catch (\Throwable $e) {
            // 通知が飛ばなくてもログイン自体は成立させる
            Log::warning('ログイン通知メールの送信に失敗しました。', [
                'user_id' => $user->id(),
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
