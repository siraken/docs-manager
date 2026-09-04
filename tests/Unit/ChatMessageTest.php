<?php

use App\Domain\Chat\Entity\ChatMessage;
use App\Domain\Shared\Exception\InvalidValueException;

/**
 * チャットの発言。
 *
 * 参考にした novalumo/e-learning は検証が一切無く、$request->body を
 * そのまま保存していた。
 */

test('発言を投稿できる', function () {
    $message = ChatMessage::post(1, 'おはようございます');

    expect($message->userId())->toBe(1)
        ->and($message->body())->toBe('おはようございます')
        ->and($message->id())->toBeNull();
});

test('前後の空白は落とす', function () {
    expect(ChatMessage::post(1, '  発言  ')->body())->toBe('発言');
});

test('空の発言は投稿できない', function () {
    ChatMessage::post(1, '');
})->throws(InvalidValueException::class);

test('空白だけの発言は投稿できない', function () {
    ChatMessage::post(1, "  \n\t ");
})->throws(InvalidValueException::class);

test('長すぎる発言は投稿できない', function () {
    ChatMessage::post(1, str_repeat('あ', ChatMessage::MAX_LENGTH + 1));
})->throws(InvalidValueException::class);

test('上限ちょうどの発言は投稿できる', function () {
    $body = str_repeat('あ', ChatMessage::MAX_LENGTH);

    expect(ChatMessage::post(1, $body)->body())->toBe($body);
});

test('改行を含む発言も投稿できる', function () {
    // 参考実装のカラムは string(255) だったが、改行を含む発言が入る
    expect(ChatMessage::post(1, "1 行目\n2 行目")->body())->toBe("1 行目\n2 行目");
});

test('消せるのは自分の発言だけ', function () {
    // 人の発言を消せると議論の記録が一方的に失われる
    $message = ChatMessage::post(1, '発言');

    expect($message->isDeletableBy(1))->toBeTrue()
        ->and($message->isDeletableBy(2))->toBeFalse();
});

test('保存済みの発言は投稿日時を持つ', function () {
    $message = ChatMessage::reconstitute(1, 2, '発言', new DateTimeImmutable('2026-09-04 14:30:00'));

    expect($message->id())->toBe(1)
        ->and($message->postedAt()?->format('Y-m-d H:i'))->toBe('2026-09-04 14:30');
});
