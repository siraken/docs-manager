<?php

use App\Domain\Order\ValueObject\TaxRate;
use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 税区分。
 *
 * 移行前は App\Lib\Common::getTaxes() の配列、フロントの TAX_RATES、
 * Blade の option タグに同じ内容が 3 重に書かれていた。サーバー側の正はこの enum。
 */

test('税区分の値と税率が対応する', function () {
    expect(TaxRate::Standard->rate())->toBe(0.1)
        ->and(TaxRate::ReducedFood->rate())->toBe(0.08)
        ->and(TaxRate::EightPercent->rate())->toBe(0.08)
        ->and(TaxRate::FivePercent->rate())->toBe(0.05)
        ->and(TaxRate::Exempt->rate())->toBe(0.0);
});

test('選択肢は5種類で、フォームの表示名と一致する', function () {
    expect(TaxRate::options())->toBe([
        1 => '10%',
        2 => '軽減8%',
        3 => '8%',
        4 => '5%',
        5 => '対象外',
    ]);
});

test('未入力は標準税率として扱う', function () {
    expect(TaxRate::fromNullable(null))->toBe(TaxRate::Standard)
        ->and(TaxRate::fromNullable(''))->toBe(TaxRate::Standard);
});

test('文字列で届いた値も解釈する', function () {
    // sqlite は integer カラムを文字列で返すことがある
    expect(TaxRate::fromNullable('2'))->toBe(TaxRate::ReducedFood);
});

test('未知の税区分は拒否する', function () {
    expect(fn () => TaxRate::fromNullable(99))->toThrow(InvalidValueException::class);
});
