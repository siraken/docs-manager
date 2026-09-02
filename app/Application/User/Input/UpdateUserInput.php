<?php

declare(strict_types=1);

namespace App\Application\User\Input;

/**
 * ユーザー編集の入力。
 *
 * password / nfcPin は「空なら現在値を維持する」項目。フォームがこれらを
 * 常に空で描画する (既存の値を画面に出さない) ためで、空文字と
 * 「消したい」を区別できないのは移行前からの仕様。
 *
 * TODO: NFC 資格情報の削除手段が無い。フォームに「NFC ログインを解除する」
 *       チェックボックスを足し、明示的に null を渡せるようにすること。
 */
final readonly class UpdateUserInput
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $password,
        public ?string $nfcSerialNumber,
        public ?string $nfcPin,
        public ?string $walletAddress,
    ) {
    }

    public function wantsPasswordChange(): bool
    {
        return $this->password !== null && $this->password !== '';
    }
}
