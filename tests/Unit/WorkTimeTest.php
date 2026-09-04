<?php

use App\Domain\Report\ValueObject\TimeOfDay;
use App\Domain\Report\ValueObject\WorkTime;
use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 勤務時間の値オブジェクト。
 *
 * 移植元は float の時間を手入力させたうえ、始業・終業も別に入力させており、
 * 両者が食い違っても誰も気付けなかった。
 */

test('分から作れる', function () {
    expect(WorkTime::fromMinutes(450)->minutes)->toBe(450)
        ->and(WorkTime::fromMinutes(450)->hours())->toBe(7.5)
        ->and(WorkTime::fromMinutes(450)->format())->toBe('7:30');
});

test('負の分は受け付けない', function () {
    WorkTime::fromMinutes(-1);
})->throws(InvalidValueException::class);

test('時間の小数から作れる', function () {
    expect(WorkTime::fromHours('7.5')->minutes)->toBe(450)
        ->and(WorkTime::fromHours(8)->minutes)->toBe(480);
});

test('未入力の勤務時間は0になる', function () {
    // 移植元の float カラムは nullable だった
    expect(WorkTime::fromHours(null)->isZero())->toBeTrue()
        ->and(WorkTime::fromHours('')->isZero())->toBeTrue();
});

test('数値として読めない勤務時間は弾かれる', function () {
    WorkTime::fromHours('半日');
})->throws(InvalidValueException::class);

test('分に満たない端数は切り捨てる', function () {
    // 7.501 h = 450.06 分。Money が円未満を切り捨てるのと同じ扱い
    expect(WorkTime::fromHours('7.501')->minutes)->toBe(450);
});

test('始業から終業までを勤務時間にできる', function () {
    $workTime = WorkTime::between(TimeOfDay::fromString('09:00'), TimeOfDay::fromString('18:30'));

    expect($workTime->minutes)->toBe(570)
        ->and($workTime->format())->toBe('9:30');
});

test('日を跨ぐ勤務でも負にならない', function () {
    // 22:00 出社 - 02:00 退社。移植元は勤務時間が手入力だったため
    // この状況をそもそも扱えなかった
    $workTime = WorkTime::between(TimeOfDay::fromString('22:00'), TimeOfDay::fromString('02:00'));

    expect($workTime->minutes)->toBe(240)
        ->and($workTime->format())->toBe('4:00');
});

test('始業と終業が同じなら24時間として扱う', function () {
    // 「0 分」ではなく「まる 1 日」。日跨ぎの規則を素直に延長したもの
    expect(WorkTime::between(TimeOfDay::fromString('09:00'), TimeOfDay::fromString('09:00'))->minutes)
        ->toBe(1440);
});

test('勤務時間は足し合わせられる', function () {
    $total = WorkTime::fromMinutes(450)->add(WorkTime::fromMinutes(90));

    expect($total->minutes)->toBe(540)
        ->and($total->format())->toBe('9:00');
});

test('10時間を超えても時間の桁は詰めない', function () {
    expect(WorkTime::fromMinutes(605)->format())->toBe('10:05');
});
