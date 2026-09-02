<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use App\Domain\Shared\Exception\InvalidValueException;

/**
 * MetaMask ログインで使うウォレットアドレス (EOA)。
 *
 * FIXME: アドレスを知っているだけでログインできてしまう。アドレスは公開情報なので
 *        本来は nonce に署名させ、署名を検証する (EIP-4361 / personal_sign) 必要がある。
 * TODO: nonce 発行 → クライアント署名 → ecrecover による検証に置き換えること。
 *       署名検証は Infrastructure 層のポート実装として足すのが自然。
 */
final readonly class WalletAddress implements \Stringable
{
    private function __construct(public string $value)
    {
    }

    public static function fromString(?string $value): self
    {
        $value = trim((string) $value);

        if (preg_match('/^0x[0-9a-fA-F]{40}$/', $value) !== 1) {
            throw new InvalidValueException(sprintf('ウォレットアドレスの形式が不正です: %s', $value));
        }

        // 大文字小文字はチェックサム表現の差でしかないため小文字に正規化する
        return new self(strtolower($value));
    }

    /**
     * 永続化層からの復元。形式検証を行わない。
     *
     * このカラムは移行前にフォームからの値を素通しで保存していたため、
     * 42 文字のアドレスになっていない行が既存 DB に残っている可能性がある。
     * 一覧やログイン処理が既存行で例外にならないよう、復元経路では正規化だけ行う。
     *
     * TODO: 既存データを移行して不正な行を落としたら、このメソッドを廃止して
     *       fromString() に一本化すること。
     */
    public static function fromStorage(string $value): self
    {
        return new self(strtolower(trim($value)));
    }

    public function matches(string $address): bool
    {
        return hash_equals($this->value, strtolower(trim($address)));
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
