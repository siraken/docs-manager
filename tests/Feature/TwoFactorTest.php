<?php

use App\Domain\User\ValueObject\TwoFactorSecret;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Inertia\Testing\AssertableInertia;

/**
 * 二段階認証の設定。
 *
 * 移行前は register_2fa_auth() が空のシークレットと空の QR URL を返すだけで、
 * 検証も保存も無かった (カラムだけが存在した)。
 */

beforeEach(function () {
    actingAsUser(createUser());
});

/** 設定画面を開き、発行されたシークレットを取り出す */
function startSetup(int $userId): TwoFactorSecret
{
    return TwoFactorSecret::fromString(twoFactorSetupProps($userId)['secret']);
}

/**
 * 設定画面の setup props。
 *
 * 画面は Inertia なので viewData() では取れない (Blade のビューにしか使えない)。
 *
 * @return array{secret: string, uri: string, alreadyEnabled: bool}
 */
function twoFactorSetupProps(int $userId): array
{
    $setup = null;

    test()->get('/users/2fa/' . $userId)
        ->assertInertia(function (AssertableInertia $page) use (&$setup): void {
            $page->component('Users/TwoFactor');
            $setup = $page->toArray()['props']['setup'];
        });

    return $setup;
}

/** そのシークレットの、いま有効なコード */
function currentCode(TwoFactorSecret $secret): string
{
    $method = new ReflectionMethod($secret, 'codeAt');

    return $method->invoke($secret, intdiv(time(), 30));
}

test('設定画面でシークレットが発行される', function () {
    $user = User::first();
    $secret = startSetup($user->id);

    expect((string) $secret)->toMatch('/^[A-Z2-7]{32}$/');
});

test('画面を開いただけでは有効にならない', function () {
    $user = User::first();
    startSetup($user->id);

    // コードの検証が通るまでユーザーのレコードには書かない
    expect(User::find($user->id)->two_factor_secret_code)->toBeNull();
});

test('正しいコードで有効になる', function () {
    $user = User::first();
    $secret = startSetup($user->id);

    $this->post('/users/2fa/' . $user->id, ['code' => currentCode($secret)])
        ->assertRedirect('/users');

    expect(User::find($user->id)->two_factor_secret_code)->toBe((string) $secret);
});

test('誤ったコードでは有効にならない', function () {
    $user = User::first();
    startSetup($user->id);

    $this->post('/users/2fa/' . $user->id, ['code' => '000000'])
        ->assertSessionHas('flash_status', 'danger');

    expect(User::find($user->id)->two_factor_secret_code)->toBeNull();
});

test('桁数が違うコードは検証以前に弾かれる', function () {
    $user = User::first();
    startSetup($user->id);

    $this->post('/users/2fa/' . $user->id, ['code' => '12345'])
        ->assertSessionHasErrors('code');
});

test('設定画面を開かずにコードだけ送っても有効にならない', function () {
    $user = User::first();

    $this->post('/users/2fa/' . $user->id, ['code' => '123456'])
        ->assertSessionHas('flash_status', 'danger');

    expect(User::find($user->id)->two_factor_secret_code)->toBeNull();
});

test('有効化済みのユーザーには画面で警告が出る', function () {
    $user = User::first();
    $secret = startSetup($user->id);
    $this->post('/users/2fa/' . $user->id, ['code' => currentCode($secret)]);

    expect(twoFactorSetupProps($user->id)['alreadyEnabled'])->toBeTrue();
});
