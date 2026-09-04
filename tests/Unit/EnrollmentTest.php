<?php

use App\Domain\Learning\Entity\Enrollment;
use App\Domain\Learning\ValueObject\EnrollmentStatus;
use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 受講記録。状態と日付が必ず噛み合うことを守る。
 *
 * 参考にした e-learning は records テーブルが id と timestamps しか無く、
 * 受講という概念そのものが未実装だった。
 */
function enrollment(
    EnrollmentStatus $status,
    ?string $startedAt = null,
    ?string $completedAt = null,
): Enrollment {
    return Enrollment::create(
        userId: 1,
        courseId: 2,
        status: $status,
        startedAt: $startedAt === null ? null : new DateTimeImmutable($startedAt),
        completedAt: $completedAt === null ? null : new DateTimeImmutable($completedAt),
        note: null,
    );
}

test('未受講なら日付を持たない', function () {
    // 状態だけ戻して日付が取り残される、という不整合を作れないようにする
    $e = enrollment(EnrollmentStatus::NotStarted, '2026-09-01', '2026-09-30');

    expect($e->startedAt())->toBeNull()
        ->and($e->completedAt())->toBeNull();
});

test('受講中なら完了日を持たない', function () {
    $e = enrollment(EnrollmentStatus::InProgress, '2026-09-01', '2026-09-30');

    expect($e->startedAt()?->format('Y-m-d'))->toBe('2026-09-01')
        ->and($e->completedAt())->toBeNull();
});

test('完了なら完了日を持つ', function () {
    $e = enrollment(EnrollmentStatus::Completed, '2026-09-01', '2026-09-30');

    expect($e->completedAt()?->format('Y-m-d'))->toBe('2026-09-30');
});

test('完了日なしで完了にはできない', function () {
    enrollment(EnrollmentStatus::Completed, '2026-09-01', null);
})->throws(InvalidValueException::class);

test('完了日が開始日より前だと作れない', function () {
    enrollment(EnrollmentStatus::Completed, '2026-09-30', '2026-09-01');
})->throws(InvalidValueException::class);

test('開始日なしでも完了にはできる', function () {
    // 過去の受講をあとからまとめて記録する場合に開始日が分からないことがある
    $e = enrollment(EnrollmentStatus::Completed, null, '2026-09-30');

    expect($e->startedAt())->toBeNull()
        ->and($e->completedAt()?->format('Y-m-d'))->toBe('2026-09-30');
});

test('ポイントが入るのは完了したときだけ', function () {
    expect(enrollment(EnrollmentStatus::Completed, null, '2026-09-30')->earnsExperience())->toBeTrue()
        ->and(enrollment(EnrollmentStatus::InProgress, '2026-09-01')->earnsExperience())->toBeFalse()
        ->and(enrollment(EnrollmentStatus::NotStarted)->earnsExperience())->toBeFalse();
});

test('更新でも状態と日付が整えられる', function () {
    $e = enrollment(EnrollmentStatus::Completed, '2026-09-01', '2026-09-30');

    // 受講中に戻すと完了日は落ちる
    $e->update(1, 2, EnrollmentStatus::InProgress, new DateTimeImmutable('2026-09-01'), new DateTimeImmutable('2026-09-30'), null);

    expect($e->status())->toBe(EnrollmentStatus::InProgress)
        ->and($e->completedAt())->toBeNull();
});

test('保存済みの受講記録は整合を通さずに読み直せる', function () {
    // 規則を後から足したときに過去の行が読めなくなるのを防ぐ
    $e = Enrollment::reconstitute(1, 1, 2, EnrollmentStatus::Completed, null, null, null);

    expect($e->id())->toBe(1)
        ->and($e->completedAt())->toBeNull();
});

test('状態の表示名が定義されている', function () {
    expect(EnrollmentStatus::NotStarted->label())->toBe('未受講')
        ->and(EnrollmentStatus::InProgress->label())->toBe('受講中')
        ->and(EnrollmentStatus::Completed->label())->toBe('完了')
        ->and(EnrollmentStatus::options())->toHaveCount(3);
});
