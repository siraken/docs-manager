<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use App\Domain\Shared\Exception\InvalidValueException;

/**
 * NFC カードによるログインの資格情報。
 *
 * FIXME: シリアル番号と PIN を平文で保存している。ログイン時に
 *        「送られてきた値と保存値の完全一致」で照合しているためで、
 *        ハッシュ化すると現在のログイン経路が動かなくなる。
 * TODO: PIN をハッシュ化し、照合を hash_equals ベースに変えること。
 *       その際シリアル番号は検索キーとして使うため平文のまま (または
 *       決定的ハッシュ) にする必要がある。
 */
final readonly class NfcCredential
{
    private function __construct(
        public string $serialNumber,
        public string $pin,
    ) {
    }

    public static function of(?string $serialNumber, ?string $pin): self
    {
        $serialNumber = trim((string) $serialNumber);
        $pin = trim((string) $pin);

        if ($serialNumber === '') {
            throw new InvalidValueException('NFC のシリアル番号が空です。');
        }

        if ($pin === '') {
            throw new InvalidValueException('NFC の PIN が空です。');
        }

        return new self($serialNumber, $pin);
    }

    /** 保存値と照合する。タイミング攻撃を避けるため hash_equals を使う */
    public function matches(string $serialNumber, string $pin): bool
    {
        return hash_equals($this->serialNumber, $serialNumber)
            && hash_equals($this->pin, $pin);
    }
}
