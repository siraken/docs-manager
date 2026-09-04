<?php

use App\Domain\Report\Entity\Report;
use App\Domain\Report\ValueObject\TimeOfDay;
use App\Domain\Report\ValueObject\WorkTime;

/**
 * 勤務報告の集約。
 *
 * 保存される勤務時間は「始業・終業が揃っていればそこから計算し、
 * 揃っていなければフォームの申告値を使う」。発注書の金額をサーバー側で
 * 計算し直しているのと同じ考え方。
 *
 * ヘルパ名は他のテストファイルと衝突しないようにしている
 * (Pest はテストファイル内の関数もグローバルスコープに入れるため)。
 */
function newReport(
    ?string $startTime,
    ?string $endTime,
    mixed $declaredHours = null,
): Report {
    return Report::create(
        userId: 1,
        customerId: 2,
        projectId: 3,
        title: '実装',
        description: null,
        date: new DateTimeImmutable('2026-09-04'),
        startTime: $startTime === null ? null : TimeOfDay::fromString($startTime),
        endTime: $endTime === null ? null : TimeOfDay::fromString($endTime),
        declaredWorkTime: WorkTime::fromHours($declaredHours),
    );
}

test('始業と終業が揃っていれば申告値ではなく計算値を使う', function () {
    // 申告は 3 時間だが、09:00-18:00 なので 9 時間になる
    $report = newReport('09:00', '18:00', '3');

    expect($report->workTime()->minutes)->toBe(540)
        ->and($report->workTime()->format())->toBe('9:00');
});

test('始業だけなら申告値を使う', function () {
    expect(newReport('09:00', null, '7.5')->workTime()->minutes)->toBe(450);
});

test('終業だけなら申告値を使う', function () {
    expect(newReport(null, '18:00', '7.5')->workTime()->minutes)->toBe(450);
});

test('時刻も申告も無ければ0時間になる', function () {
    expect(newReport(null, null, null)->workTime()->isZero())->toBeTrue();
});

test('更新でも勤務時間が計算し直される', function () {
    $report = newReport('09:00', '18:00', '3');

    $report->update(
        userId: 1,
        customerId: 2,
        projectId: 3,
        title: '実装',
        description: null,
        date: new DateTimeImmutable('2026-09-04'),
        startTime: TimeOfDay::fromString('10:00'),
        endTime: TimeOfDay::fromString('15:30'),
        declaredWorkTime: WorkTime::fromHours('99'),
    );

    expect($report->workTime()->minutes)->toBe(330);
});

test('時刻を消すと申告値に切り替わる', function () {
    $report = newReport('09:00', '18:00', null);

    $report->update(
        userId: 1,
        customerId: 2,
        projectId: 3,
        title: '実装',
        description: null,
        date: new DateTimeImmutable('2026-09-04'),
        startTime: null,
        endTime: null,
        declaredWorkTime: WorkTime::fromHours('4'),
    );

    expect($report->workTime()->minutes)->toBe(240);
});

test('新規作成の時点では id を持たない', function () {
    $report = newReport('09:00', '18:00');

    expect($report->id())->toBeNull();

    $report->assignId(7);

    expect($report->id())->toBe(7);
});
