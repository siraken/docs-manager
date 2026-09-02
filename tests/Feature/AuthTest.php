<?php

/**
 * 独自セッション認証 (LoginController + LoginMiddleware) のリグレッションテスト。
 *
 * このアプリは Illuminate\Auth を使わず、session('user_id'/'name'/'email') を
 * 手動で組み立てている。Laravel のバージョンを上げた際にこの前提が崩れて
 * いないことを検出する。
 */

test('未認証では保護されたルートがログイン画面にリダイレクトされる', function () {
    $this->get('/orders')->assertRedirect('/login');
    $this->get('/')->assertRedirect('/login');
    $this->get('/users')->assertRedirect('/login');
});

test('ログイン画面は未認証でも表示できる', function () {
    $this->get('/login')->assertOk();
});

test('正しい認証情報でセッションが張られる', function () {
    $user = createUser();

    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'secret123',
    ]);

    $response->assertRedirect('/');
    $response->assertSessionHas('user_id', $user->id);
    $response->assertSessionHas('name', 'テスト太郎');
    $response->assertSessionHas('email', 'test@example.com');
    $response->assertSessionHas('flash_status', 'success');
});

test('パスワードが違う場合はログイン画面に戻される', function () {
    createUser();

    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionMissing('user_id');
    $response->assertSessionHas('flash_status', 'danger');
});

test('存在しないユーザーはログイン画面に戻される', function () {
    createUser();

    $response = $this->post('/login', [
        'email' => 'nobody@example.com',
        'password' => 'secret123',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionMissing('user_id');
});

test('存在しないユーザーとパスワード誤りで応答が変わらない', function () {
    // 移行前は前者だけ "The user does not exist." と表示しており、
    // メールアドレスが登録済みかどうかを外から判別できた。
    createUser();

    $missing = $this->post('/login', ['email' => 'nobody@example.com', 'password' => 'secret123']);
    $wrong = $this->post('/login', ['email' => 'test@example.com', 'password' => 'wrong-password']);

    expect($missing->getSession()->get('flash_message'))
        ->toBe($wrong->getSession()->get('flash_message'));
});

test('ログイン時にセッションIDが再生成される', function () {
    createUser();

    $before = session()->getId();

    $this->post('/login', ['email' => 'test@example.com', 'password' => 'secret123']);

    // セッション固定攻撃を避けるため、ログインの前後で ID が変わる
    expect(session()->getId())->not->toBe($before);
});

test('ユーザーが一人もいない場合はユーザー作成画面へ誘導される', function () {
    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'secret123',
    ]);

    $response->assertRedirect('/users/create');
});

test('セッションがあれば保護ルートにアクセスできる', function () {
    actingAsUser(createUser())->get('/orders')->assertOk();
});

test('ログアウトでセッションが破棄される', function () {
    $response = actingAsUser(createUser())->post('/logout');

    $response->assertRedirect('/login');
    $response->assertSessionMissing('user_id');
    $response->assertSessionMissing('name');
});

test('NFCのシリアルとPINが一致すればログインできる', function () {
    $user = createUser([
        'nfc_serial_number' => 'AA:BB:CC:DD',
        'nfc_pin' => '1234',
    ]);

    $response = $this->post('/login/login-nfc', [
        'serialNumber' => 'AA:BB:CC:DD',
        'pin' => '1234',
    ]);

    $response->assertRedirect('/');
    $response->assertSessionHas('user_id', $user->id);
});

test('NFCのPINが違えばログインできない', function () {
    createUser([
        'nfc_serial_number' => 'AA:BB:CC:DD',
        'nfc_pin' => '1234',
    ]);

    $response = $this->post('/login/login-nfc', [
        'serialNumber' => 'AA:BB:CC:DD',
        'pin' => '9999',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionMissing('user_id');
});

test('ウォレットアドレスが一致すればログインできる', function () {
    $user = createUser(['wallet_address' => walletAddress('abc')]);

    $response = $this->post('/login/login-metamask', ['address' => walletAddress('abc')]);

    $response->assertRedirect('/');
    $response->assertSessionHas('user_id', $user->id);
});

test('ウォレットアドレスの大文字小文字は区別しない', function () {
    // チェックサム表現の差でログインできなくならないようにする
    $user = createUser(['wallet_address' => '0xAbCdEf0000000000000000000000000000000001']);

    $response = $this->post('/login/login-metamask', [
        'address' => '0xabcdef0000000000000000000000000000000001',
    ]);

    $response->assertSessionHas('user_id', $user->id);
});

test('形式が不正な既存データでもログインできる', function () {
    // 移行前はアドレスを検証せずに保存していたため、42 文字でない値が
    // 既存 DB に残っている可能性がある。復元経路では形式検証をしない。
    $user = createUser(['wallet_address' => '0xabc123']);

    $this->post('/login/login-metamask', ['address' => '0xabc123'])
        ->assertSessionHas('user_id', $user->id);
});

test('未登録のウォレットアドレスではログインできない', function () {
    createUser(['wallet_address' => walletAddress('abc')]);

    $response = $this->post('/login/login-metamask', ['address' => walletAddress('def')]);

    $response->assertRedirect('/login');
    $response->assertSessionMissing('user_id');
});
