<?php

use App\Domain\Project\ValueObject\ProjectStatus;

/**
 * 案件の状態。
 *
 * 移行前は「フォームの選択肢が 8 種類、一覧の表示が 3 種類」という食い違いがあり、
 * さらに一覧側は switch (true) の誤用で status = 0 が「進行中」と表示されていた。
 */

test('8種類の状態を持つ', function () {
    expect(ProjectStatus::options())->toBe([
        0 => '作業中',
        1 => '完了',
        2 => '連絡待ち',
        3 => '保留',
        4 => '打診中',
        5 => 'メンテナンス',
        6 => 'キャンセル',
        7 => '見積中',
    ]);
});

test('status = 0 は作業中', function () {
    // 移行前はここが「進行中」と表示されていた (switch の誤用)
    expect(ProjectStatus::fromNullable(0)->label())->toBe('作業中');
});

test('文字列で届いた値も解釈する', function () {
    // sqlite は integer カラムを文字列で返すことがある
    expect(ProjectStatus::fromNullable('2'))->toBe(ProjectStatus::AwaitingReply);
});

test('未設定と未知の値は作業中に倒す', function () {
    expect(ProjectStatus::fromNullable(null))->toBe(ProjectStatus::InProgress)
        ->and(ProjectStatus::fromNullable(''))->toBe(ProjectStatus::InProgress)
        ->and(ProjectStatus::fromNullable(99))->toBe(ProjectStatus::InProgress);
});
