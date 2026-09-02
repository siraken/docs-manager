<?php

declare(strict_types=1);

namespace App\Application\Auth\Exception;

use App\Domain\Shared\Exception\DomainException;

/**
 * 認証に失敗したことを表す。
 *
 * 「ユーザーが存在しない」と「パスワードが違う」を呼び出し側から区別できると
 * メールアドレスの存在を外部に教えることになるため、メッセージは既定で同じにする。
 * 移行前は前者だけ "The user does not exist." と表示していた。
 */
final class AuthenticationFailedException extends DomainException
{
    public function __construct(string $message = 'メールアドレスまたはパスワードが正しくありません。')
    {
        parent::__construct($message);
    }
}
