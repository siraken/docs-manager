<?php

use App\Domain\Accounting\ValueObject\AccountType;
use App\Domain\Project\ValueObject\ProjectStatus;
use App\Support\SelectOptions;

/**
 * enum の options() を画面のセレクト用に整える変換。
 *
 * 各コントローラが同じ private メソッドを持っていたのを集約したもの。
 */

test('連想配列を value と label の並びにする', function () {
    expect(SelectOptions::fromMap(['a' => 'あ', 'b' => 'い']))
        ->toBe([
            ['value' => 'a', 'label' => 'あ'],
            ['value' => 'b', 'label' => 'い'],
        ]);
});

test('整数のキーも扱える', function () {
    // 案件の状態は int の enum
    $options = SelectOptions::fromMap(ProjectStatus::options());

    expect($options)->toHaveCount(8)
        ->and($options[0])->toBe(['value' => 0, 'label' => '作業中']);
});

test('文字列のキーも扱える', function () {
    $options = SelectOptions::fromMap(AccountType::options());

    expect($options)->toHaveCount(5)
        ->and($options[0])->toBe(['value' => 'asset', 'label' => '資産']);
});

test('空の連想配列からは空の並びができる', function () {
    expect(SelectOptions::fromMap([]))->toBe([]);
});
