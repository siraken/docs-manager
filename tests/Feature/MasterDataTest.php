<?php

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * ユーザー / 顧客 / 案件のリグレッションテスト。
 */

beforeEach(function () {
    actingAsUser(createUser());
});

// --- ユーザー -------------------------------------------------------

test('ユーザーを作成するとパスワードがハッシュ化される', function () {
    $this->post('/users/create', [
        'name' => '新規ユーザー',
        'email' => 'new@example.com',
        'password' => 'plain-password',
    ])->assertRedirect('/users');

    $created = User::where('email', 'new@example.com')->first();
    expect($created)->not->toBeNull()
        ->and($created->password)->not->toBe('plain-password')
        ->and(Hash::check('plain-password', $created->password))->toBeTrue();
});

test('パスワード未入力の編集では既存のハッシュが維持される', function () {
    $original = createUser(['email' => 'edit@example.com', 'password' => Hash::make('original-password')]);
    $originalHash = $original->password;

    $this->post('/users/edit/' . $original->id, [
        'name' => '編集後',
        'email' => 'edit@example.com',
        'password' => '',
    ]);

    $updated = User::find($original->id)->makeVisible(['password']);
    expect($updated->name)->toBe('編集後')
        ->and($updated->password)->toBe($originalHash)
        ->and(Hash::check('original-password', $updated->password))->toBeTrue();
});

test('パスワードを入力した編集では新しいハッシュになる', function () {
    $original = createUser(['email' => 'edit@example.com', 'password' => Hash::make('original-password')]);

    $this->post('/users/edit/' . $original->id, [
        'name' => '編集対象',
        'email' => 'edit@example.com',
        'password' => 'new-password',
    ]);

    $updated = User::find($original->id)->makeVisible(['password']);
    expect(Hash::check('new-password', $updated->password))->toBeTrue()
        ->and(Hash::check('original-password', $updated->password))->toBeFalse();
});

test('NFCとウォレットアドレスは平文のまま保存される', function () {
    $user = createUser(['email' => 'edit@example.com']);

    $this->post('/users/edit/' . $user->id, [
        'name' => '編集対象',
        'email' => 'edit@example.com',
        'password' => '',
        'nfc_serial_number' => 'AA:BB:CC',
        'nfc_pin' => '1234',
        'wallet_address' => '0xabc',
    ]);

    // ログイン時に平文比較しているため、保存も平文である必要がある
    $updated = User::find($user->id)->makeVisible(['nfc_serial_number', 'nfc_pin']);
    expect($updated->nfc_serial_number)->toBe('AA:BB:CC')
        ->and($updated->nfc_pin)->toBe('1234')
        ->and($updated->wallet_address)->toBe('0xabc');
});

test('ユーザー一覧が表示できる', function () {
    $this->get('/users')->assertOk();
});

// --- 顧客 -----------------------------------------------------------

test('顧客を作成できる', function () {
    $this->post('/customers/create', [
        'name' => '株式会社サンプル',
        'is_company' => '1',
        'email' => 'sample@example.com',
        'phone' => '03-1111-2222',
        'post_code' => '150-0001',
        'address' => '神宮前1-1',
        'city' => '渋谷区',
        'state' => '東京都',
        'country' => '日本',
        'note' => 'メモ',
    ])->assertRedirect('/customers');

    $customer = Customer::first();
    expect($customer->name)->toBe('株式会社サンプル')
        ->and((int) $customer->is_company)->toBe(1);
});

test('顧客編集はPOSTしても保存されない', function () {
    $customer = new Customer();
    $customer->name = '変更前';
    $customer->is_company = 1;
    $customer->save();

    $this->post('/customers/edit/' . $customer->id, [
        'name' => '変更後',
        'is_company' => '1',
    ]);

    // CustomerController::edit() には POST 分岐が無く、常に view を返すだけ。
    // 現状の挙動 (保存されない) を記録する。
    expect(Customer::find($customer->id)->name)->toBe('変更前');
});

test('顧客一覧が表示できる', function () {
    $this->get('/customers')->assertOk();
});

// --- 案件 -----------------------------------------------------------

test('案件を作成できる', function () {
    $this->post('/projects/create', [
        'name' => 'テスト案件',
        'description' => '説明',
        'client_id' => 1,
        'related_task_id' => null,
        'start_date' => '2026-09-01',
        'end_date' => '2026-12-31',
        'payment_date' => '2027-01-31',
        'price' => 500000,
        'status' => 1,
    ])->assertRedirect();

    $project = Project::first();
    expect($project)->not->toBeNull()
        ->and($project->name)->toBe('テスト案件')
        ->and((int) $project->price)->toBe(500000);
});

test('案件一覧のステータス表示は壊れている', function () {
    // ProjectController::index() は
    //   switch ($project->status) { case $project->status === 0: ... }
    // と書かれており、case に真偽値が並んでいる (switch (true) の誤用)。
    // status = 0 のとき最初の case (0 == true) が偽、次の case (0 == false) が
    // 真になるため、「未着手」ではなく「進行中」と表示される。
    //
    // なおこの結果は値の型に依存する。PHP 8.1 で PDO SQLite が integer を
    // native type で返すようになったため、PHP 7.4 時代 (string が返り
    // status 1・2 が「未知」になっていた) とは挙動が変わっている。
    // MySQL は元から integer を返すので、この結果が本番の挙動に近い。
    foreach ([0, 1, 2] as $status) {
        $p = new Project();
        $p->name = '案件' . $status;
        $p->status = $status;
        $p->save();
    }

    $response = $this->get('/projects');
    $response->assertOk();

    $projects = $response->viewData('projects')->keyBy('name');
    // 0 は「未着手」であるべきだが「進行中」になる (これがバグ)
    expect($projects['案件0']->status)->toBe('進行中')
        ->and($projects['案件1']->status)->toBe('進行中')
        ->and($projects['案件2']->status)->toBe('完了');
});
