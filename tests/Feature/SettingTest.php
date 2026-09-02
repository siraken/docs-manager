<?php

use App\Infrastructure\Persistence\Eloquent\Models\Setting;

/**
 * 設定 (自社情報)。
 *
 * 移行前は /settings が「設定項目はまだありません」と出すだけのクロージャで、
 * settings テーブルを読むコードも書くコードも無かった。
 */

beforeEach(function () {
    actingAsUser(createUser());
});

test('未登録なら既定値が表示される', function () {
    $response = $this->get('/settings');

    $response->assertOk();
    // 移行前に PDF へ直書きされていた値が既定値になっている
    $response->assertSee('Novalumo合同会社');
    expect($response->viewData('profile')->isDefault)->toBeTrue();
});

test('自社情報を保存できる', function () {
    $this->post('/settings', [
        'name' => 'テスト商会',
        'zipcode' => '100-0001',
        'address' => "東京都千代田区1-1\nテストビル 3F",
        'rep' => '代表 太郎',
        'tel_no' => '03-1234-5678',
        'logo_url' => 'img/Logo.png',
        'com_stamp_url' => 'img/CompanyStamp.png',
    ])->assertRedirect('/settings');

    $setting = Setting::first();
    expect($setting->name)->toBe('テスト商会')
        ->and($setting->tel_no)->toBe('03-1234-5678');

    $response = $this->get('/settings');
    $response->assertSee('テスト商会');
    expect($response->viewData('profile')->isDefault)->toBeFalse();
});

test('保存は既存のレコードを更新する', function () {
    $this->post('/settings', ['name' => '1回目']);
    $this->post('/settings', ['name' => '2回目']);

    expect(Setting::count())->toBe(1)
        ->and(Setting::first()->name)->toBe('2回目');
});

test('会社名が空だと保存できない', function () {
    $this->post('/settings', ['name' => ''])->assertSessionHasErrors('name');

    expect(Setting::count())->toBe(0);
});

test('画像パスに親ディレクトリ参照は使えない', function () {
    $this->post('/settings', [
        'name' => 'テスト商会',
        'logo_url' => '../../../etc/passwd',
    ])->assertSessionHasErrors('logo_url');
});
