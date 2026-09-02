<?php

use App\Infrastructure\Persistence\Eloquent\Models\LumoUser;

/**
 * Lumo Academy の問い合わせ。
 *
 * 移行前は register() が fill() を呼ぶだけで save() しておらず、送信された
 * 内容がどこにも残らないまま 200 を返していた。index() は中身が空だった。
 */

test('問い合わせを登録できる', function () {
    $response = $this->postJson('/lumo-academy/register', [
        'name' => '問い合わせ太郎',
        'email' => 'inquiry@example.com',
        'body' => 'カリキュラムについて教えてください',
    ]);

    $response->assertCreated();
    $response->assertJson(['status' => 'ok']);

    $saved = LumoUser::first();
    expect($saved)->not->toBeNull()
        ->and($saved->name)->toBe('問い合わせ太郎')
        ->and($saved->email)->toBe('inquiry@example.com')
        ->and($saved->inquiry)->toBe('カリキュラムについて教えてください');
});

test('inquiry という項目名でも受け付ける', function () {
    // 移行前のコントローラは body を読んでいたが、保存先のカラムは inquiry
    $this->postJson('/lumo-academy/register', [
        'name' => '太郎',
        'email' => 'inquiry@example.com',
        'inquiry' => '本文',
    ])->assertCreated();

    expect(LumoUser::first()->inquiry)->toBe('本文');
});

test('本文が無いと登録できない', function () {
    $this->postJson('/lumo-academy/register', [
        'name' => '太郎',
        'email' => 'inquiry@example.com',
    ])->assertStatus(422);

    expect(LumoUser::count())->toBe(0);
});

test('メールアドレスの形式が不正だと登録できない', function () {
    $this->postJson('/lumo-academy/register', [
        'name' => '太郎',
        'email' => 'not-an-email',
        'body' => '本文',
    ])->assertStatus(422);
});

test('登録は認証不要', function () {
    // 外部サイトのフォームから叩かれる公開エンドポイント
    $this->postJson('/lumo-academy/register', [
        'name' => '太郎',
        'email' => 'inquiry@example.com',
        'body' => '本文',
    ])->assertCreated();
});

test('一覧は認証が必要', function () {
    $this->get('/lumo-academy')->assertRedirect('/login');
});

test('一覧に登録済みの問い合わせが出る', function () {
    LumoUser::create([
        'name' => '問い合わせ太郎',
        'email' => 'inquiry@example.com',
        'inquiry' => 'カリキュラムについて',
    ]);

    $response = actingAsUser(createUser())->get('/lumo-academy');

    $response->assertOk();
    $response->assertSee('問い合わせ太郎');
    $response->assertSee('カリキュラムについて');
});
