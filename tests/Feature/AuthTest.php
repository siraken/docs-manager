<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * 独自セッション認証 (LoginController + LoginMiddleware) のリグレッションテスト。
 *
 * このアプリは Illuminate\Auth を使わず、session('user_id'/'name'/'email') を
 * 手動で組み立てている。Laravel のバージョンを上げた際にこの前提が崩れて
 * いないことを検出する。
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => Hash::make('secret123'),
        ], $attributes));
    }

    public function test_未認証では保護されたルートがログイン画面にリダイレクトされる(): void
    {
        $this->get('/orders')->assertRedirect('/login');
        $this->get('/')->assertRedirect('/login');
        $this->get('/users')->assertRedirect('/login');
    }

    public function test_ログイン画面は未認証でも表示できる(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_正しい認証情報でセッションが張られる(): void
    {
        $user = $this->createUser();

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('user_id', $user->id);
        $response->assertSessionHas('name', 'テスト太郎');
        $response->assertSessionHas('email', 'test@example.com');
        $response->assertSessionHas('flash_status', 'success');
    }

    public function test_パスワードが違う場合はログイン画面に戻される(): void
    {
        $this->createUser();

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionMissing('user_id');
        $response->assertSessionHas('flash_status', 'danger');
    }

    public function test_存在しないユーザーはログイン画面に戻される(): void
    {
        $this->createUser();

        $response = $this->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionMissing('user_id');
    }

    public function test_ユーザーが一人もいない場合はユーザー作成画面へ誘導される(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/users/create');
    }

    public function test_セッションがあれば保護ルートにアクセスできる(): void
    {
        $user = $this->createUser();

        $this->withSession([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ])->get('/orders')->assertOk();
    }

    public function test_ログアウトでセッションが破棄される(): void
    {
        $user = $this->createUser();

        $response = $this->withSession([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ])->post('/logout');

        $response->assertRedirect('/login');
        $response->assertSessionMissing('user_id');
        $response->assertSessionMissing('name');
    }

    public function test_NFCのシリアルとPINが一致すればログインできる(): void
    {
        $user = $this->createUser([
            'nfc_serial_number' => 'AA:BB:CC:DD',
            'nfc_pin' => '1234',
        ]);

        $response = $this->post('/login/login-nfc', [
            'serialNumber' => 'AA:BB:CC:DD',
            'pin' => '1234',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('user_id', $user->id);
    }

    public function test_NFCのPINが違えばログインできない(): void
    {
        $this->createUser([
            'nfc_serial_number' => 'AA:BB:CC:DD',
            'nfc_pin' => '1234',
        ]);

        $response = $this->post('/login/login-nfc', [
            'serialNumber' => 'AA:BB:CC:DD',
            'pin' => '9999',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionMissing('user_id');
    }

    public function test_ウォレットアドレスが一致すればログインできる(): void
    {
        $user = $this->createUser(['wallet_address' => '0xabc123']);

        $response = $this->post('/login/login-metamask', ['address' => '0xabc123']);

        $response->assertRedirect('/');
        $response->assertSessionHas('user_id', $user->id);
    }

    public function test_未登録のウォレットアドレスではログインできない(): void
    {
        $this->createUser(['wallet_address' => '0xabc123']);

        $response = $this->post('/login/login-metamask', ['address' => '0xdeadbeef']);

        $response->assertRedirect('/login');
        $response->assertSessionMissing('user_id');
    }
}
