<?php

declare(strict_types=1);

namespace App\Domain\Order\ValueObject;

use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 発注書番号。
 *
 * PDF / CSV のダウンロードファイル名にそのまま使われるため、パス区切りや
 * 制御文字を含む値を作れないようにしている (旧 OrderController::csv() は
 * 検証せずに './' . $order_no . '.csv' を fopen していた)。
 */
final readonly class OrderNo implements \Stringable
{
    private const MAX_LENGTH = 255;

    private function __construct(public string $value)
    {
    }

    public static function fromString(?string $value): self
    {
        $value = trim((string) $value);

        if ($value === '') {
            throw new InvalidValueException('発注書番号が空です。');
        }

        if (mb_strlen($value) > self::MAX_LENGTH) {
            throw new InvalidValueException('発注書番号が長すぎます。');
        }

        if (preg_match('#[/\\\\\x00-\x1f]#', $value) === 1) {
            throw new InvalidValueException('発注書番号にパス区切り・制御文字は使えません。');
        }

        return new self($value);
    }

    /**
     * 永続化層からの復元。形式検証を行わない。
     *
     * 移行前は番号を検証せずに保存していたため、既存 DB にパス区切りを含む行が
     * ある可能性がある。一覧が落ちないよう復元経路では通す。
     * ファイル名として使う toFileName() 側で無害化しているため、
     * ここを通した値がそのままパスに出ることはない。
     *
     * TODO: 既存データの移行後に廃止し、fromString() へ一本化すること。
     */
    public static function fromStorage(string $value): self
    {
        return new self($value);
    }

    /** ダウンロード時のファイル名。拡張子は呼び出し側が足す */
    public function toFileName(string $extension): string
    {
        // fromStorage() を通った既存データがパス区切りを含む場合に備えて無害化する
        $safe = preg_replace('#[/\\\\\x00-\x1f]#', '_', $this->value) ?? 'order';

        return $safe . '.' . ltrim($extension, '.');
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
