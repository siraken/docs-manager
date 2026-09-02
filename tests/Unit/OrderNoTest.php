<?php

use App\Domain\Order\ValueObject\OrderNo;
use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 発注書番号。
 *
 * PDF / CSV のファイル名にそのまま使われるため、パス区切りを含む値を
 * 作れないようにしている (移行前は検証せずに './' . $order_no . '.csv' を
 * fopen していた)。
 */

test('通常の番号は作れる', function () {
    expect((string) OrderNo::fromString('2026-001'))->toBe('2026-001');
});

test('前後の空白は落とす', function () {
    expect((string) OrderNo::fromString('  NO-1  '))->toBe('NO-1');
});

test('空の番号は拒否する', function () {
    expect(fn () => OrderNo::fromString(''))->toThrow(InvalidValueException::class)
        ->and(fn () => OrderNo::fromString(null))->toThrow(InvalidValueException::class);
});

test('パス区切りや制御文字を含む番号は拒否する', function () {
    expect(fn () => OrderNo::fromString('../etc/passwd'))->toThrow(InvalidValueException::class)
        ->and(fn () => OrderNo::fromString('a\\b'))->toThrow(InvalidValueException::class)
        ->and(fn () => OrderNo::fromString("a\x00b"))->toThrow(InvalidValueException::class);
});

test('ファイル名は拡張子を付けて返す', function () {
    expect(OrderNo::fromString('2026-001')->toFileName('pdf'))->toBe('2026-001.pdf')
        // 先頭のドット付きでも二重にならない
        ->and(OrderNo::fromString('2026-001')->toFileName('.csv'))->toBe('2026-001.csv');
});

test('既存データの復元では検証しないが、ファイル名では無害化する', function () {
    // 移行前に保存された行にパス区切りが含まれていても一覧が落ちないようにする
    $orderNo = OrderNo::fromStorage('../../etc/passwd');

    expect((string) $orderNo)->toBe('../../etc/passwd')
        ->and($orderNo->toFileName('csv'))->toBe('.._.._etc_passwd.csv');
});
