<?php

use App\Domain\Shared\Exception\InvalidValueException;
use App\Domain\User\ValueObject\TwoFactorSecret;

/**
 * 二段階認証 (TOTP)。
 *
 * 移行前は register_2fa_auth() が空のシークレットと空の QR URL を返すだけの
 * スタブだった。RFC 6238 のテストベクタで実装が正しいことを確認する。
 */

/** private な codeAt() を呼ぶ (テストベクタとの突き合わせに必要) */
function totpAt(TwoFactorSecret $secret, int $timestamp): string
{
    return (new ReflectionMethod($secret, 'codeAt'))->invoke($secret, intdiv($timestamp, 30));
}

test('RFC 6238 のテストベクタと一致する', function () {
    // seed は ASCII の "12345678901234567890" を Base32 にしたもの
    $secret = TwoFactorSecret::fromString('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ');

    expect(totpAt($secret, 59))->toBe('287082')
        ->and(totpAt($secret, 1111111109))->toBe('081804')
        ->and(totpAt($secret, 1234567890))->toBe('005924')
        ->and(totpAt($secret, 2000000000))->toBe('279037');
});

test('発行したシークレットは Base32 の 32 文字', function () {
    $secret = TwoFactorSecret::generate();

    expect((string) $secret)->toMatch('/^[A-Z2-7]{32}$/');
});

test('正しいコードを受け付ける', function () {
    $secret = TwoFactorSecret::generate();
    $at = new DateTimeImmutable('@1234567890');

    expect($secret->verify(totpAt($secret, 1234567890), $at))->toBeTrue();
});

test('誤ったコードを拒否する', function () {
    $secret = TwoFactorSecret::generate();
    $at = new DateTimeImmutable('@1234567890');

    expect($secret->verify('000000', $at))->toBeFalse()
        ->and($secret->verify('12345', $at))->toBeFalse()
        ->and($secret->verify('', $at))->toBeFalse();
});

test('時計のずれを前後1ウィンドウまで許容する', function () {
    $secret = TwoFactorSecret::generate();
    $code = totpAt($secret, 1234567890);
    $at = new DateTimeImmutable('@1234567890');

    expect($secret->verify($code, $at->modify('+30 seconds')))->toBeTrue()
        ->and($secret->verify($code, $at->modify('-30 seconds')))->toBeTrue()
        // 2 ウィンドウ以上ずれたら通さない
        ->and($secret->verify($code, $at->modify('+120 seconds')))->toBeFalse();
});

test('Base32 でない文字列は拒否する', function () {
    expect(fn () => TwoFactorSecret::fromString('not-base32!'))->toThrow(InvalidValueException::class)
        // 1 と 8 は Base32 のアルファベットに含まれない
        ->and(fn () => TwoFactorSecret::fromString('AAAA1111BBBB8888'))->toThrow(InvalidValueException::class);
});

test('認証アプリ用の URI を組み立てる', function () {
    $secret = TwoFactorSecret::fromString('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ');
    $uri = $secret->toUri('Novalumo Docs Manager', 'test@example.com');

    expect($uri)->toStartWith('otpauth://totp/')
        ->toContain('secret=GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ')
        ->toContain('digits=6')
        ->toContain('period=30')
        // ラベルとイシュアはパーセントエンコードされる
        ->toContain('test%40example.com');
});
