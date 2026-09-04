<?php

use App\Domain\Contract\ValueObject\ContractStatus;
use App\Domain\Contract\ValueObject\ContractTerm;
use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 契約期間と、そこから導出する状態。
 *
 * 移植元の一覧は列見出しが「契約期間」なのに開始日しか出しておらず、
 * しかも中身は存在しないカラム ($contract['pid']) を引いていた。
 */

function term(?string $start, ?string $end): ContractTerm
{
    return ContractTerm::of(
        $start === null ? null : new DateTimeImmutable($start),
        $end === null ? null : new DateTimeImmutable($end),
    );
}

test('終了日が開始日より前だと作れない', function () {
    term('2026-09-30', '2026-09-01');
})->throws(InvalidValueException::class);

test('開始日と終了日が同じ日なら作れる', function () {
    expect(term('2026-09-01', '2026-09-01')->label())->toBe('2026/09/01 〜 2026/09/01');
});

test('期間内なら契約中', function () {
    expect(term('2026-09-01', '2026-12-31')->statusOn(new DateTimeImmutable('2026-10-15')))
        ->toBe(ContractStatus::Active);
});

test('開始日と終了日の当日は契約中に含む', function () {
    $subject = term('2026-09-01', '2026-12-31');

    expect($subject->statusOn(new DateTimeImmutable('2026-09-01')))->toBe(ContractStatus::Active)
        ->and($subject->statusOn(new DateTimeImmutable('2026-12-31')))->toBe(ContractStatus::Active);
});

test('開始前と終了後は状態が変わる', function () {
    $subject = term('2026-09-01', '2026-12-31');

    expect($subject->statusOn(new DateTimeImmutable('2026-08-31')))->toBe(ContractStatus::Scheduled)
        ->and($subject->statusOn(new DateTimeImmutable('2027-01-01')))->toBe(ContractStatus::Expired);
});

test('時刻が入っていても日付だけで判定する', function () {
    // 終了日当日の 23:00 はまだ契約中
    expect(term('2026-09-01', '2026-12-31')->statusOn(new DateTimeImmutable('2026-12-31 23:00:00')))
        ->toBe(ContractStatus::Active);
});

test('終了日が無ければ期限なしとして契約中', function () {
    expect(term('2026-09-01', null)->statusOn(new DateTimeImmutable('2099-01-01')))
        ->toBe(ContractStatus::Active);
});

test('開始日が無ければ開始済みとして扱う', function () {
    expect(term(null, '2026-12-31')->statusOn(new DateTimeImmutable('2020-01-01')))
        ->toBe(ContractStatus::Active);
});

test('両端とも未設定なら常に契約中で期間はハイフン', function () {
    $subject = ContractTerm::empty();

    expect($subject->statusOn(new DateTimeImmutable('2026-09-04')))->toBe(ContractStatus::Active)
        ->and($subject->label())->toBe('-');
});

test('状態の表示名が定義されている', function () {
    expect(ContractStatus::Active->label())->toBe('契約中')
        ->and(ContractStatus::Scheduled->label())->toBe('開始前')
        ->and(ContractStatus::Expired->label())->toBe('終了');
});
