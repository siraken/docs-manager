<?php

use App\Domain\Learning\ValueObject\ExperiencePoint;
use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 講座の獲得ポイント。
 *
 * 参考にした e-learning は classes.exp という整数カラムがあるだけで、
 * 加算する処理も表示する画面も無かった。
 */

test('0以上の整数から作れる', function () {
    expect(ExperiencePoint::of(50)->value)->toBe(50)
        ->and(ExperiencePoint::zero()->isZero())->toBeTrue();
});

test('負のポイントは受け付けない', function () {
    ExperiencePoint::of(-1);
})->throws(InvalidValueException::class);

test('未入力は0になる', function () {
    // ポイントを設定しない講座もある
    expect(ExperiencePoint::fromNullable(null)->isZero())->toBeTrue()
        ->and(ExperiencePoint::fromNullable('')->isZero())->toBeTrue();
});

test('数値として読めない値は弾かれる', function () {
    ExperiencePoint::fromNullable('たくさん');
})->throws(InvalidValueException::class);

test('足し合わせられる', function () {
    $total = ExperiencePoint::of(50)->add(ExperiencePoint::of(30));

    expect($total->value)->toBe(80)
        ->and($total->format())->toBe('80');
});

test('桁区切りで表示できる', function () {
    expect(ExperiencePoint::of(12345)->format())->toBe('12,345');
});
