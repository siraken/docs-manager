<?php

use App\Domain\Project\ValueObject\JiraKey;
use App\Domain\Shared\Exception\InvalidValueException;

/**
 * Jira のキー。
 *
 * in-house-timecard-app の projects.pid を移植したもの。docs-manager 側にあった
 * related_task_id は整数で、指す先の tasks テーブルは存在せず、画面にも
 * 一切出ていなかった。
 */

test('プロジェクトキーと課題キーのどちらも受ける', function () {
    expect(JiraKey::fromString('NOVA')->value)->toBe('NOVA')
        ->and(JiraKey::fromString('NOVA-123')->value)->toBe('NOVA-123');
});

test('小文字は大文字に寄せる', function () {
    // Jira 自身の既定と同じく大文字を正とする
    expect(JiraKey::fromString('nova-123')->value)->toBe('NOVA-123');
});

test('前後の空白は落とす', function () {
    expect(JiraKey::fromString('  NOVA-1  ')->value)->toBe('NOVA-1');
});

test('数字で始まるキーは受け付けない', function () {
    JiraKey::fromString('1NOVA');
})->throws(InvalidValueException::class);

test('記号を含むキーは受け付けない', function () {
    JiraKey::fromString('NOVA/123');
})->throws(InvalidValueException::class);

test('課題番号が数字でないキーは受け付けない', function () {
    JiraKey::fromString('NOVA-ABC');
})->throws(InvalidValueException::class);

test('未入力は null になる', function () {
    // 案件に Jira を紐付けないこともある
    expect(JiraKey::parseNullable(null))->toBeNull()
        ->and(JiraKey::parseNullable(''))->toBeNull()
        ->and(JiraKey::parseNullable('   '))->toBeNull();
});

test('同じキーどうしは等しい', function () {
    expect(JiraKey::fromString('NOVA-1')->equals(JiraKey::fromString('nova-1')))->toBeTrue()
        ->and(JiraKey::fromString('NOVA-1')->equals(JiraKey::fromString('NOVA-2')))->toBeFalse();
});

test('文字列として扱える', function () {
    expect((string) JiraKey::fromString('NOVA-1'))->toBe('NOVA-1');
});
