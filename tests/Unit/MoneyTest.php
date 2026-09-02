<?php

use App\Domain\Shared\Exception\InvalidValueException;
use App\Domain\Shared\ValueObject\Money;

/**
 * 金額の値オブジェクト。
 *
 * ドメイン層は Laravel に依存しないため、フレームワークを起動しない
 * Unit テストで検証できる。
 */

test('整数から作れる', function () {
    expect(Money::fromInt(1500)->amount)->toBe(1500)
        ->and(Money::zero()->amount)->toBe(0);
});

test('文字列や null からも作れる', function () {
    // sqlite が integer カラムを文字列で返す、フォームの未入力が空文字で届く、
    // といった事情があるため数値らしい入力は受け付ける
    expect(Money::fromNumeric('1500')->amount)->toBe(1500)
        ->and(Money::fromNumeric(null)->amount)->toBe(0)
        ->and(Money::fromNumeric('')->amount)->toBe(0)
        ->and(Money::fromNumeric(1500.9)->amount)->toBe(1500);
});

test('数値として読めない値は拒否する', function () {
    expect(fn () => Money::fromNumeric('abc'))->toThrow(InvalidValueException::class);
});

test('加算と乗算ができる', function () {
    expect(Money::fromInt(1000)->add(Money::fromInt(500))->amount)->toBe(1500)
        ->and(Money::fromInt(1000)->multiply(3)->amount)->toBe(3000);
});

test('乗算の端数は切り捨てる', function () {
    // 消費税の計算で小数が出る。1000 × 8% = 80、999 × 8% = 79.92 → 79
    expect(Money::fromInt(1000)->multiply(0.08)->amount)->toBe(80)
        ->and(Money::fromInt(999)->multiply(0.08)->amount)->toBe(79);
});

test('桁区切りで表示できる', function () {
    expect(Money::fromInt(1234567)->format())->toBe('1,234,567')
        ->and((string) Money::fromInt(1500))->toBe('1500');
});
