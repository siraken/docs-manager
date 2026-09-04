<?php

use App\Domain\Chat\Entity\ChatMessage as ChatMessageEntity;
use App\Infrastructure\Persistence\Eloquent\Models\ChatMessage;
use Inertia\Testing\AssertableInertia;

/**
 * チャット。全員が読み書きする 1 つのルーム。
 *
 * novalumo/e-learning を参考に作り直したもの。参考実装で壊れていた箇所を
 * 中心に見る。
 */

beforeEach(function () {
    $this->me = createUser(['name' => '自分', 'email' => 'me@example.com']);
    actingAsUser($this->me);
});

// --- 表示 -----------------------------------------------------------

test('発言の一覧が表示できる', function () {
    createChatMessage($this->me->id, 'おはようございます');

    $this->get('/chat')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Chat/Index')
        ->has('messages', 1)
        ->where('messages.0.body', 'おはようございます')
        ->where('messages.0.userName', '自分')
        ->where('messages.0.isMine', true));
});

test('発言は古い順に並ぶ', function () {
    // 参考実装は新しい順のまま画面へ渡し、JS 側で reverse() していた
    $a = createChatMessage($this->me->id, '1 番目');
    $b = createChatMessage($this->me->id, '2 番目');
    $b->update(['created_at' => now()->addMinute()]);

    $this->get('/chat')->assertInertia(function (AssertableInertia $page): void {
        $bodies = array_column($page->toArray()['props']['messages'], 'body');
        expect($bodies)->toBe(['1 番目', '2 番目']);
    });
});

test('他人の発言には投稿者名が出る', function () {
    // 参考実装は本文しか描いておらず、誰の発言か分からなかった
    $other = createUser(['name' => '同僚', 'email' => 'other@example.com']);
    createChatMessage($other->id, 'こんにちは');

    $this->get('/chat')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('messages.0.userName', '同僚')
        ->where('messages.0.isMine', false));
});

test('投稿日時が渡る', function () {
    // 参考実装は日時を出す処理がコメントアウトされたままだった
    createChatMessage($this->me->id);

    $this->get('/chat')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('messages.0.postedAt', fn (string $v) => $v !== '')
        ->where('messages.0.postedAtLabel', fn (string $v) => $v !== ''));
});

test('読み込む件数には上限がある', function () {
    foreach (range(1, 60) as $i) {
        createChatMessage($this->me->id, '発言 ' . $i);
    }

    $this->get('/chat')->assertInertia(fn (AssertableInertia $page) => $page->has('messages', 50));
});

test('削除されたユーザーの発言でも一覧は開ける', function () {
    $other = createUser(['name' => '退職者', 'email' => 'gone@example.com']);
    createChatMessage($other->id, '発言');
    $other->delete();

    $this->get('/chat')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('messages.0.userName', '(削除されたユーザー)'));
});

// --- 投稿 -----------------------------------------------------------

test('発言を投稿できる', function () {
    $this->post('/chat', ['body' => 'よろしくお願いします'])->assertRedirect('/chat');

    $message = ChatMessage::first();
    expect($message->body)->toBe('よろしくお願いします')
        ->and((int) $message->user_id)->toBe($this->me->id);
});

test('投稿者はログイン中のユーザーになる', function () {
    // 画面から user_id を送らせると他人になりすませる。
    // 送っても無視されることを確かめる
    $other = createUser(['name' => '同僚', 'email' => 'other@example.com']);

    $this->post('/chat', ['body' => '発言', 'user_id' => $other->id]);

    expect((int) ChatMessage::first()->user_id)->toBe($this->me->id);
});

test('空の発言は投稿できない', function () {
    // 参考実装は検証が無く、空の発言がそのまま保存されていた
    $this->post('/chat', ['body' => ''])->assertSessionHasErrors('body');

    expect(ChatMessage::count())->toBe(0);
});

test('長すぎる発言は投稿できない', function () {
    $this->post('/chat', ['body' => str_repeat('あ', ChatMessageEntity::MAX_LENGTH + 1)])
        ->assertSessionHasErrors('body');

    expect(ChatMessage::count())->toBe(0);
});

test('改行を含む発言も投稿できる', function () {
    // 参考実装のカラムは string(255) だった
    $this->post('/chat', ['body' => "1 行目\n2 行目"])->assertRedirect('/chat');

    expect(ChatMessage::first()->body)->toBe("1 行目\n2 行目");
});

test('投稿してもフラッシュは出さない', function () {
    // 発言のたびにトーストが出ると会話の邪魔になる
    $this->post('/chat', ['body' => '発言']);

    $this->get('/chat')->assertInertia(fn (AssertableInertia $page) => $page->where('flash', null));
});

// --- 削除 -----------------------------------------------------------

test('自分の発言は削除できる', function () {
    // 参考実装に削除の手段は無かった
    $message = createChatMessage($this->me->id);

    $this->delete('/chat/delete/' . $message->id)->assertRedirect('/chat');

    expect(ChatMessage::count())->toBe(0);
});

test('他人の発言は削除できない', function () {
    $other = createUser(['name' => '同僚', 'email' => 'other@example.com']);
    $message = createChatMessage($other->id);

    $this->delete('/chat/delete/' . $message->id);

    expect(ChatMessage::count())->toBe(1);

    $this->get('/chat')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('flash.status', 'danger')
        ->where('flash.message', fn (string $m) => str_contains($m, '自分の発言だけ')));
});

test('他人の発言には削除のURLが渡らない', function () {
    $other = createUser(['name' => '同僚', 'email' => 'other@example.com']);
    createChatMessage($other->id);

    $this->get('/chat')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('messages.0.urls', null));
});

test('存在しない発言は404になる', function () {
    $this->delete('/chat/delete/999')->assertNotFound();
});

test('未認証ではチャットにアクセスできない', function () {
    session()->flush();

    $this->get('/chat')->assertRedirect('/login');
});
