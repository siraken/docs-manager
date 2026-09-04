<?php

use App\Domain\Accounting\Entity\JournalEntry;
use App\Domain\Shared\Exception\InvalidValueException;
use App\Domain\Shared\ValueObject\Money;

/**
 * 仕訳の成立条件。
 *
 * 参考にした移植元は借方金額と貸方金額を別々に入力させ、検証も無かったため、
 * 貸借がずれた仕訳を作れてしまっていた。
 */
function newEntry(int $debitId = 1, int $creditId = 2, int $amount = 1000): JournalEntry
{
    return JournalEntry::create(
        new DateTimeImmutable('2026-09-04'),
        $debitId,
        $creditId,
        Money::fromInt($amount),
        '売上の計上',
        null,
    );
}

test('借方と貸方と金額を持つ', function () {
    $entry = newEntry();

    expect($entry->debitAccountId())->toBe(1)
        ->and($entry->creditAccountId())->toBe(2)
        ->and($entry->amount()->amount)->toBe(1000)
        ->and($entry->date()->format('Y-m-d'))->toBe('2026-09-04');
});

test('借方と貸方に同じ科目は指定できない', function () {
    // 「現金 / 現金」は何も動かない
    newEntry(debitId: 1, creditId: 1);
})->throws(InvalidValueException::class);

test('金額が0円の仕訳は作れない', function () {
    newEntry(amount: 0);
})->throws(InvalidValueException::class);

test('金額が負の仕訳は作れない', function () {
    newEntry(amount: -100);
})->throws(InvalidValueException::class);

test('更新でも同じ検証が効く', function () {
    $entry = newEntry();

    $entry->update(
        new DateTimeImmutable('2026-09-05'),
        3,
        3,
        Money::fromInt(500),
        '摘要',
        null,
    );
})->throws(InvalidValueException::class);

test('更新できる', function () {
    $entry = newEntry();

    $entry->update(
        new DateTimeImmutable('2026-09-05'),
        3,
        4,
        Money::fromInt(500),
        '変更後',
        'メモ',
    );

    expect($entry->debitAccountId())->toBe(3)
        ->and($entry->creditAccountId())->toBe(4)
        ->and($entry->amount()->amount)->toBe(500)
        ->and($entry->description())->toBe('変更後')
        ->and($entry->note())->toBe('メモ');
});

test('保存済みの仕訳は検証を通さずに読み直せる', function () {
    // 規則を後から足したときに、過去の行が読めなくなって一覧ごと
    // 開けなくなるのを避ける
    $entry = JournalEntry::reconstitute(
        1,
        new DateTimeImmutable('2020-01-01'),
        1,
        1, // いまの規則では作れない仕訳
        Money::zero(),
        '過去の行',
        null,
    );

    expect($entry->id())->toBe(1)
        ->and($entry->amount()->isZero())->toBeTrue();
});

test('新規作成の時点ではidを持たない', function () {
    $entry = newEntry();

    expect($entry->id())->toBeNull();

    $entry->assignId(7);

    expect($entry->id())->toBe(7);
});
