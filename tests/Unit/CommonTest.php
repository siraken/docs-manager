<?php

use App\Lib\Common;

/**
 * App\Lib\Common の現状の挙動を固定するリグレッションテスト。
 *
 * 仕様の正しさを主張するものではなく、Laravel / PHP のバージョンを
 * 上げた際に戻り値が変わっていないことを検出するのが目的。
 */

beforeEach(function () {
    $this->common = new Common();
});

test('calcPer は百分率を掛けた値を返す', function () {
    expect($this->common->calcPer(1000, 10))->toBe(100.0)
        ->and($this->common->calcPer(1000, 8))->toBe(80.0)
        ->and($this->common->calcPer(1000, 0))->toBe(0.0)
        ->and($this->common->calcPer(0, 10))->toBe(0.0);
});

test('calcPer は小数を丸めない', function () {
    // 端数処理を入れていないため、浮動小数がそのまま返る
    expect($this->common->calcPer(1001, 8))->toEqualWithDelta(80.08, 0.0001);
});

test('getMonths は1から12のゼロ埋め文字列を返す', function () {
    $months = $this->common->getMonths();

    expect($months)->toHaveCount(12)
        ->and($months[1])->toBe('01')
        ->and($months[9])->toBe('09')
        ->and($months[10])->toBe('10')
        ->and($months[12])->toBe('12')
        // キーは 1 始まりの整数
        ->and(array_keys($months))->toBe(range(1, 12));
});

test('getTaxes は5種類の税区分を返す', function () {
    expect($this->common->getTaxes())->toBe([
        ['id' => 1, 'name' => '10%',    'per' => 0.1],
        ['id' => 2, 'name' => '軽減8%', 'per' => 0.08],
        ['id' => 3, 'name' => '8%',     'per' => 0.08],
        ['id' => 4, 'name' => '5%',     'per' => 0.05],
        ['id' => 5, 'name' => '対象外', 'per' => 0],
    ]);
});
