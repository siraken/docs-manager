<?php

use Illuminate\Http\UploadedFile;

/**
 * ファイルの受け渡し。
 *
 * 移行前は routes/web.php のクロージャで $_POST / $_FILES を直接読み、
 * ファイル名を検証せずに storage のパスへ連結していた。
 */

function uploadsDir(): string
{
    $dir = storage_path('app/public/uploads');

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    return $dir;
}

beforeEach(function () {
    // 各テストが前のテストのファイルを見ないように掃除する
    foreach (glob(uploadsDir() . '/*') ?: [] as $file) {
        unlink($file);
    }
});

test('ファイルをアップロードできる', function () {
    $response = $this->post('/uploader', [
        'name' => '山田',
        'email' => 'yamada@example.com',
        'file' => UploadedFile::fake()->createWithContent('report.txt', 'hello'),
    ]);

    $response->assertRedirect('/downloader');
    expect(file_exists(uploadsDir() . '/山田_report.txt'))->toBeTrue();
});

test('ファイル名のパス区切りは無害化される', function () {
    $this->post('/uploader', [
        'name' => '../../evil',
        'file' => UploadedFile::fake()->createWithContent('../../../pwned.txt', 'x'),
    ]);

    // 保存先ディレクトリの外にファイルができていないこと
    expect(file_exists(base_path('pwned.txt')))->toBeFalse()
        ->and(file_exists(storage_path('pwned.txt')))->toBeFalse()
        ->and(file_exists(storage_path('app/pwned.txt')))->toBeFalse()
        ->and(file_exists(uploadsDir() . '/evil_pwned.txt'))->toBeTrue();
});

test('ファイル無しのアップロードは弾かれる', function () {
    $this->post('/uploader', ['name' => '山田'])->assertSessionHasErrors('file');
});

test('アップロードは未ログインでもできる', function () {
    // 取引先にファイルを送ってもらう想定のため認証を掛けていない
    $this->post('/uploader', [
        'file' => UploadedFile::fake()->createWithContent('guest.txt', 'x'),
    ])->assertRedirect('/downloader');
});

test('一覧は未ログインだと出ない', function () {
    file_put_contents(uploadsDir() . '/secret.txt', 'x');

    $response = $this->get('/downloader');

    $response->assertOk();
    $response->assertDontSee('secret.txt');
});

test('一覧はログイン中なら出る', function () {
    file_put_contents(uploadsDir() . '/secret.txt', 'x');

    actingAsUser(createUser())->get('/downloader')->assertSee('secret.txt');
});

test('ダウンロードできる', function () {
    file_put_contents(uploadsDir() . '/report.txt', 'hello');

    $response = actingAsUser(createUser())->get('/downloader/report.txt');

    $response->assertOk();
    // Server ヘッダを足すミドルウェアが BinaryFileResponse でも動くこと。
    // 移行前は $response->header() を呼んでいたため、この経路は必ず
    // "Call to undefined method BinaryFileResponse::header()" で 500 になっていた。
    $response->assertHeader('Server', 'Novalumo Server');
    expect($response->streamedContent())->toBe('hello');
});

test('保存先の外を指すダウンロードは404になる', function () {
    actingAsUser(createUser())->get('/downloader/' . urlencode('../../.env'))
        ->assertNotFound();
});

test('ファイルを削除できる', function () {
    file_put_contents(uploadsDir() . '/report.txt', 'hello');

    actingAsUser(createUser())->delete('/downloader/delete/report.txt')
        ->assertRedirect('/downloader');

    expect(file_exists(uploadsDir() . '/report.txt'))->toBeFalse();
});

test('保存先の外を指す削除は404になる', function () {
    actingAsUser(createUser())->delete('/downloader/delete/' . urlencode('../../.env'))
        ->assertNotFound();

    expect(file_exists(base_path('.env')))->toBeTrue();
});
