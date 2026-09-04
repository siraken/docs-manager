<?php

use App\Domain\Report\ValueObject\TimeOfDay;
use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 時刻の値オブジェクト。
 *
 * reports.start_time / end_time は time 型で、MySQL は "09:00:00"、
 * sqlite は入れた文字列をそのまま返すため表現がぶれる。境界でここに寄せる。
 */

test('HH:MM と HH:MM:SS のどちらでも読める', function () {
    expect(TimeOfDay::fromString('09:00')->format())->toBe('09:00')
        ->and(TimeOfDay::fromString('09:00:00')->format())->toBe('09:00')
        ->and(TimeOfDay::fromString('9:05')->format())->toBe('09:05');
});

test('形式が不正な時刻は弾かれる', function () {
    TimeOfDay::fromString('9時');
})->throws(InvalidValueException::class);

test('存在しない時刻は弾かれる', function () {
    TimeOfDay::fromString('25:00');
})->throws(InvalidValueException::class);

test('未入力の時刻は null になる', function () {
    // 始業・終業はどちらも任意入力
    expect(TimeOfDay::parseNullable(null))->toBeNull()
        ->and(TimeOfDay::parseNullable(''))->toBeNull();
});

test('DateTime からも作れる', function () {
    // Eloquent が datetime へキャストして返す場合に備える
    expect(TimeOfDay::parseNullable(new DateTimeImmutable('2026-09-04 13:45:00'))?->format())
        ->toBe('13:45');
});

test('同じ時刻どうしは等しい', function () {
    expect(TimeOfDay::fromString('09:00')->equals(TimeOfDay::fromString('09:00:00')))->toBeTrue()
        ->and(TimeOfDay::fromString('09:00')->equals(TimeOfDay::fromString('09:01')))->toBeFalse();
});
