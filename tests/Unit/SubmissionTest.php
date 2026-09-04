<?php

use App\Domain\Learning\Entity\Assignment;
use App\Domain\Learning\Entity\Submission;
use App\Domain\Learning\ValueObject\SubmissionStatus;
use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 提出物。状態と提出日・講評が必ず噛み合うことを守る。
 *
 * 参考にした novalumo/e-learning は tasks テーブルが id と timestamps しか
 * 持たず、課題そのものが未実装だった。
 */
function submission(
    SubmissionStatus $status,
    ?string $submittedAt = null,
    ?string $feedback = null,
): Submission {
    return Submission::create(
        assignmentId: 1,
        userId: 2,
        status: $status,
        submittedAt: $submittedAt === null ? null : new DateTimeImmutable($submittedAt),
        body: '提出内容',
        feedback: $feedback,
    );
}

function assignmentDue(?string $dueOn): Assignment
{
    return Assignment::reconstitute(
        1,
        1,
        '課題',
        null,
        $dueOn === null ? null : new DateTimeImmutable($dueOn),
    );
}

test('未提出なら提出日も講評も持たない', function () {
    $s = submission(SubmissionStatus::NotSubmitted, '2026-09-10', '良い');

    expect($s->submittedAt())->toBeNull()
        ->and($s->feedback())->toBeNull();
});

test('提出日なしで提出済みにはできない', function () {
    submission(SubmissionStatus::Submitted, null);
})->throws(InvalidValueException::class);

test('提出済みでは講評を持たない', function () {
    // 提出されただけの段階でまだ評価はしていない
    $s = submission(SubmissionStatus::Submitted, '2026-09-10', 'まだ見ていないのに講評');

    expect($s->submittedAt()?->format('Y-m-d'))->toBe('2026-09-10')
        ->and($s->feedback())->toBeNull();
});

test('差し戻しでは講評を持てる', function () {
    $s = submission(SubmissionStatus::Returned, '2026-09-10', 'ここを直してください');

    expect($s->feedback())->toBe('ここを直してください');
});

test('合格でも講評を持てる', function () {
    expect(submission(SubmissionStatus::Approved, '2026-09-10', 'よくできています')->feedback())
        ->toBe('よくできています');
});

test('未提出に戻すと提出日と講評が落ちる', function () {
    $s = submission(SubmissionStatus::Approved, '2026-09-10', '合格');

    $s->update(1, 2, SubmissionStatus::NotSubmitted, new DateTimeImmutable('2026-09-10'), null, '合格');

    expect($s->submittedAt())->toBeNull()
        ->and($s->feedback())->toBeNull();
});

test('期限に遅れた提出を判定できる', function () {
    $assignment = assignmentDue('2026-09-10');

    expect(submission(SubmissionStatus::Submitted, '2026-09-11')->isLate($assignment))->toBeTrue()
        ->and(submission(SubmissionStatus::Submitted, '2026-09-09')->isLate($assignment))->toBeFalse();
});

test('期限当日の提出は遅れていない', function () {
    expect(submission(SubmissionStatus::Submitted, '2026-09-10')->isLate(assignmentDue('2026-09-10')))
        ->toBeFalse();
});

test('期限が無ければ遅れようがない', function () {
    expect(submission(SubmissionStatus::Submitted, '2099-01-01')->isLate(assignmentDue(null)))
        ->toBeFalse();
});

test('未提出は遅れたとは呼ばない', function () {
    // まだ出していないだけ。期限切れかどうかは一覧側が今日の日付で判定する
    expect(submission(SubmissionStatus::NotSubmitted)->isLate(assignmentDue('2020-01-01')))
        ->toBeFalse();
});

test('課題の期限切れを判定できる', function () {
    $assignment = assignmentDue('2026-09-10');

    expect($assignment->isOverdueOn(new DateTimeImmutable('2026-09-11')))->toBeTrue()
        ->and($assignment->isOverdueOn(new DateTimeImmutable('2026-09-10')))->toBeFalse()
        ->and(assignmentDue(null)->isOverdueOn(new DateTimeImmutable('2099-01-01')))->toBeFalse();
});

test('保存済みの提出物は整合を通さずに読み直せる', function () {
    $s = Submission::reconstitute(1, 1, 2, SubmissionStatus::Approved, null, null, null);

    expect($s->id())->toBe(1)
        ->and($s->submittedAt())->toBeNull();
});

test('状態の表示名が定義されている', function () {
    expect(SubmissionStatus::NotSubmitted->label())->toBe('未提出')
        ->and(SubmissionStatus::Submitted->label())->toBe('提出済み')
        ->and(SubmissionStatus::Returned->label())->toBe('差し戻し')
        ->and(SubmissionStatus::Approved->label())->toBe('合格')
        ->and(SubmissionStatus::options())->toHaveCount(4);
});
