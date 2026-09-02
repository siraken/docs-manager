<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 二段階認証 (TOTP) の共有シークレット。users.two_factor_secret_code に入る。
 *
 * 移行前は register_2fa_auth() が空文字を返すだけのスタブで、認証コードの
 * 検証も QR コードの生成も無かった。TOTP (RFC 6238) は HMAC-SHA1 だけで
 * 実装できるため、外部ライブラリを増やさずここに置いている。
 *
 * TODO: QR コード画像の生成は未実装。いまは otpauth:// URI を画面に出して
 *       認証アプリに手入力してもらう形になっている。画像が要るなら
 *       bacon/bacon-qr-code などを足し、Infrastructure 層のポートとして
 *       実装を差し込むこと (ドメインに画像生成を持ち込まない)。
 * TODO: リカバリコードは未実装。端末を失うとログインできなくなるため、
 *       運用に載せる前に用意すること。
 */
final readonly class TwoFactorSecret implements \Stringable
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /** 認証コードの桁数 */
    private const DIGITS = 6;

    /** コードが変わる周期 (秒) */
    private const PERIOD = 30;

    /** 時計のずれを許容する前後のウィンドウ数 */
    private const LEEWAY = 1;

    private function __construct(public string $value)
    {
    }

    public static function fromString(?string $value): self
    {
        $value = strtoupper(trim((string) $value));

        if (preg_match('/^[A-Z2-7]{16,}$/', $value) !== 1) {
            throw new InvalidValueException('二段階認証のシークレットが Base32 形式ではありません。');
        }

        return new self($value);
    }

    /** 新しいシークレットを発行する (160bit を Base32 で 32 文字) */
    public static function generate(): self
    {
        $bytes = random_bytes(20);
        $secret = '';
        $buffer = 0;
        $bitsLeft = 0;

        foreach (str_split($bytes) as $byte) {
            $buffer = ($buffer << 8) | ord($byte);
            $bitsLeft += 8;

            while ($bitsLeft >= 5) {
                $bitsLeft -= 5;
                $secret .= self::ALPHABET[($buffer >> $bitsLeft) & 31];
            }
        }

        if ($bitsLeft > 0) {
            $secret .= self::ALPHABET[($buffer << (5 - $bitsLeft)) & 31];
        }

        return new self($secret);
    }

    /**
     * 認証アプリに登録するための otpauth URI。
     * issuer / label は認証アプリの一覧に表示される名前になる。
     */
    public function toUri(string $issuer, string $accountName): string
    {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=%d&period=%d',
            rawurlencode($issuer),
            rawurlencode($accountName),
            $this->value,
            rawurlencode($issuer),
            self::DIGITS,
            self::PERIOD,
        );
    }

    /**
     * 認証アプリが出したコードを検証する。
     * 端末の時計ずれを見込んで前後 1 ウィンドウ (±30 秒) まで許容する。
     */
    public function verify(string $code, \DateTimeImmutable $at): bool
    {
        $code = preg_replace('/\D/', '', $code) ?? '';

        if (strlen($code) !== self::DIGITS) {
            return false;
        }

        $counter = intdiv($at->getTimestamp(), self::PERIOD);

        for ($offset = -self::LEEWAY; $offset <= self::LEEWAY; $offset++) {
            if (hash_equals($this->codeAt($counter + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    /** 指定カウンタにおける HOTP 値 (RFC 4226 の動的切り出し) */
    private function codeAt(int $counter): string
    {
        $hash = hash_hmac('sha1', pack('J', $counter), $this->decode(), true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0f;

        $binary = ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff);

        return str_pad((string) ($binary % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /** Base32 文字列をバイト列に戻す */
    private function decode(): string
    {
        $buffer = 0;
        $bitsLeft = 0;
        $bytes = '';

        foreach (str_split($this->value) as $char) {
            $index = strpos(self::ALPHABET, $char);

            if ($index === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $index;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $bytes .= chr(($buffer >> $bitsLeft) & 0xff);
            }
        }

        return $bytes;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
