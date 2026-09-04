<?php

use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use App\Infrastructure\Persistence\Eloquent\Models\Project;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;

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

test('新規登録画面が表示できる', function () {
    // 移行前は 2FA リンクが route('users.2fa', ['id' => null]) を組もうとして
    // Missing required parameter で 500 になっていた。
    $this->get('/users/create')->assertOk();
});

test('パスワードが短すぎる場合は登録できない', function () {
    $this->post('/users/create', [
        'name' => '新規ユーザー',
        'email' => 'new@example.com',
        'password' => 'short',
    ])->assertSessionHasErrors('password');

    expect(User::where('email', 'new@example.com')->exists())->toBeFalse();
});

test('メールアドレスが重複する場合は登録できない', function () {
    $this->post('/users/create', [
        'name' => '重複ユーザー',
        'email' => 'test@example.com', // createUser と同じ
        'password' => 'plain-password',
    ])->assertSessionHasErrors('email');

    expect(User::count())->toBe(1);
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
    $wallet = walletAddress('abc');

    $this->post('/users/edit/' . $user->id, [
        'name' => '編集対象',
        'email' => 'edit@example.com',
        'password' => '',
        'nfc_serial_number' => 'AA:BB:CC',
        'nfc_pin' => '1234',
        'wallet_address' => $wallet,
    ]);

    // ログイン時に平文比較しているため、保存も平文である必要がある
    $updated = User::find($user->id)->makeVisible(['nfc_serial_number', 'nfc_pin']);
    expect($updated->nfc_serial_number)->toBe('AA:BB:CC')
        ->and($updated->nfc_pin)->toBe('1234')
        ->and($updated->wallet_address)->toBe($wallet);
});

test('NFCのPIN未入力では既存のPINが維持される', function () {
    $user = createUser([
        'email' => 'edit@example.com',
        'nfc_serial_number' => 'AA:BB:CC',
        'nfc_pin' => '1234',
    ]);

    $this->post('/users/edit/' . $user->id, [
        'name' => '編集対象',
        'email' => 'edit@example.com',
        'password' => '',
        'nfc_serial_number' => 'DD:EE:FF',
        'nfc_pin' => '',
    ]);

    $updated = User::find($user->id)->makeVisible(['nfc_serial_number', 'nfc_pin']);
    expect($updated->nfc_serial_number)->toBe('DD:EE:FF')
        ->and($updated->nfc_pin)->toBe('1234');
});

test('NFCシリアルを空にするとNFCログインが無効になる', function () {
    $user = createUser([
        'email' => 'edit@example.com',
        'nfc_serial_number' => 'AA:BB:CC',
        'nfc_pin' => '1234',
    ]);

    $this->post('/users/edit/' . $user->id, [
        'name' => '編集対象',
        'email' => 'edit@example.com',
        'password' => '',
        'nfc_serial_number' => '',
    ]);

    $updated = User::find($user->id)->makeVisible(['nfc_serial_number', 'nfc_pin']);
    expect($updated->nfc_serial_number)->toBeNull()
        ->and($updated->nfc_pin)->toBeNull();
});

test('形式が不正なウォレットアドレスは保存できない', function () {
    $user = createUser(['email' => 'edit@example.com']);

    $this->post('/users/edit/' . $user->id, [
        'name' => '編集対象',
        'email' => 'edit@example.com',
        'password' => '',
        'wallet_address' => '0xabc123', // 42 文字ではない
    ])->assertSessionHasErrors('wallet_address');

    expect(User::find($user->id)->wallet_address)->toBeNull();
});

test('ユーザー一覧が表示できる', function () {
    $this->get('/users')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Users/Index')
        ->has('users', 1)
        ->where('users.0.email', 'test@example.com')
        ->where('users.0.hasTwoFactor', false));
});

test('未保存のユーザーには編集や2FAのURLが無い', function () {
    // 移行前はビューが route('users.2fa', ['id' => null]) を組もうとして 500 になっていた。
    $this->get('/users/create')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Users/Form')
        ->where('user', null));
});

test('ユーザーの編集画面には既存の値と2FAのURLが渡る', function () {
    $user = User::first();

    $this->get('/users/edit/' . $user->id)->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Users/Form')
        ->where('user.name', $user->name)
        ->where('user.urls.twoFactor', url('/users/2fa/' . $user->id)));
});

test('ユーザーを削除できる', function () {
    // 移行前は一覧に削除ボタンがあるのに、未定義の JS 関数を呼ぶだけで
    // サーバー側の受け口も無かった。
    $target = createUser(['email' => 'target@example.com']);

    $this->delete('/users/delete/' . $target->id)->assertRedirect('/users');

    expect(User::find($target->id))->toBeNull()
        ->and(User::count())->toBe(1);
});

test('最後のユーザーは削除できない', function () {
    $only = User::first();

    $this->delete('/users/delete/' . $only->id);

    expect(User::count())->toBe(1);
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

test('顧客を編集できる', function () {
    // 移行前は edit() に POST 分岐が無く、保存ボタンを押しても
    // フォームを描き直すだけで何も起きなかった。
    $customer = Customer::create(['name' => '変更前', 'is_company' => 1]);

    $this->post('/customers/edit/' . $customer->id, [
        'name' => '変更後',
        'is_company' => '1',
        'email' => 'after@example.com',
        'city' => '港区',
    ])->assertRedirect('/customers');

    $updated = Customer::find($customer->id);
    expect($updated->name)->toBe('変更後')
        ->and($updated->email)->toBe('after@example.com')
        ->and($updated->city)->toBe('港区');
});

test('法人チェックを外すと個人になる', function () {
    $customer = Customer::create(['name' => '法人', 'is_company' => 1]);

    // チェックボックスは未チェックだと送信されない
    $this->post('/customers/edit/' . $customer->id, ['name' => '個人']);

    expect((int) Customer::find($customer->id)->is_company)->toBe(0);
});

test('顧客名が空だと保存できない', function () {
    $this->post('/customers/create', ['name' => ''])
        ->assertSessionHasErrors('name');

    expect(Customer::count())->toBe(0);
});

test('顧客に担当者名を保存できる', function () {
    // in-house-timecard-app の clients.person から移植した項目。
    // 移植前の docs-manager は担当者を発注書ごとにしか持てなかった。
    $this->post('/customers/create', [
        'name' => '株式会社サンプル',
        'person' => '営業一郎',
    ])->assertRedirect('/customers');

    expect(Customer::first()->person)->toBe('営業一郎');
});

test('発注書フォームの取引先セレクトに担当者名が載る', function () {
    // 取引先を選んだときに担当者欄を埋めるために使う
    createCustomer('担当者つき商会')->update(['person' => '営業一郎']);

    $this->get('/orders/create')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Orders/Form')
        ->where('customers.0.person', '営業一郎'));
});

test('顧客一覧が表示できる', function () {
    createCustomer('一覧に出る商会');

    $this->get('/customers')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Customers/Index')
        ->has('customers', 1)
        ->where('customers.0.name', '一覧に出る商会')
        ->where('customers.0.isCompany', true));
});

test('顧客の編集画面には既存の値が渡る', function () {
    $customer = createCustomer('編集対象');

    $this->get('/customers/edit/' . $customer->id)->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Customers/Form')
        ->where('customer.name', '編集対象')
        ->where('customer.email', 'client@example.com'));
});

// --- 案件 -----------------------------------------------------------

test('案件を作成できる', function () {
    $customer = createCustomer('案件の取引先');

    $this->post('/projects/create', [
        'name' => 'テスト案件',
        'description' => '説明',
        'client_id' => $customer->id,
        'jira_key' => 'NOVA-123',
        'start_date' => '2026-09-01',
        'end_date' => '2026-12-31',
        'payment_date' => '2027-01-31',
        'price' => 500000,
        'status' => 1,
    ])->assertRedirect('/projects');

    $project = Project::first();
    expect($project)->not->toBeNull()
        ->and($project->name)->toBe('テスト案件')
        // price は移行前の $fillable から漏れていた
        ->and((int) $project->price)->toBe(500000)
        ->and((int) $project->status)->toBe(1)
        ->and($project->jira_key)->toBe('NOVA-123');
});

test('案件を編集できる', function () {
    $project = Project::create(['name' => '変更前', 'status' => 0, 'price' => 100]);

    $this->post('/projects/edit/' . $project->id, [
        'name' => '変更後',
        'price' => 200,
        'status' => 3,
    ])->assertRedirect('/projects');

    $updated = Project::find($project->id);
    expect($updated->name)->toBe('変更後')
        ->and((int) $updated->price)->toBe(200)
        ->and((int) $updated->status)->toBe(3);
});

test('案件一覧のステータス表示がフォームの選択肢と一致する', function () {
    // 移行前は
    //   switch ($project->status) { case $project->status === 0: ... }
    // という switch (true) の誤用で、status = 0 が「進行中」と表示されていた。
    // さらに一覧は 3 種類、フォームは 8 種類という食い違いもあった。
    $expected = [
        0 => '作業中',
        1 => '完了',
        2 => '連絡待ち',
        3 => '保留',
        4 => '打診中',
        5 => 'メンテナンス',
        6 => 'キャンセル',
        7 => '見積中',
    ];

    foreach (array_keys($expected) as $status) {
        Project::create(['name' => '案件' . $status, 'status' => $status]);
    }

    $this->get('/projects')->assertInertia(function (AssertableInertia $page) use ($expected): void {
        $page->component('Projects/Index');

        $projects = collect($page->toArray()['props']['projects'])->keyBy('name');

        foreach ($expected as $status => $label) {
            expect($projects['案件' . $status]['status'])->toBe($label);
        }
    });
});

test('売上分析は指定した年月の案件だけを集計する', function () {
    Project::create(['name' => '対象1', 'payment_date' => '2026-09-15', 'price' => 100000]);
    Project::create(['name' => '対象2', 'payment_date' => '2026-09-30', 'price' => 200000]);
    Project::create(['name' => '対象外', 'payment_date' => '2026-10-01', 'price' => 999999]);

    $this->get('/projects/analysis?type=payment_date&year=2026&month=9')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Projects/Analysis')
            ->has('projects', 2)
            ->where('totalPrice', 300000));
});

test('案件の編集画面には状態の選択肢が8種類渡る', function () {
    // 移行前はフォームのビューに 8 種類、一覧のコントローラに 3 種類という
    // 食い違った定義が別々に書かれていた。いまは ProjectStatus が唯一の定義。
    $project = Project::create(['name' => '編集対象', 'status' => 3]);

    $this->get('/projects/edit/' . $project->id)->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Projects/Form')
        ->where('project.statusValue', 3)
        ->has('statuses', 8));
});

test('案件の一覧には取引先名とJiraのリンクが渡る', function () {
    // 移植前の一覧は取引先の列に顧客 ID をそのまま出しており、
    // Jira への導線はそもそも無かった (related_task_id は画面に出ない死に項目)。
    $customer = createCustomer('案件の取引先');
    Project::create(['name' => 'リンク確認', 'client_id' => $customer->id, 'jira_key' => 'NOVA-9']);

    $this->get('/projects')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('projects.0.clientName', '案件の取引先')
        ->where('projects.0.jiraKey', 'NOVA-9')
        ->where('projects.0.jiraUrl', config('services.jira.browse_url') . 'NOVA-9'));
});

test('Jiraのキーが無ければリンクも無い', function () {
    Project::create(['name' => 'Jira なし']);

    $this->get('/projects')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('projects.0.jiraKey', null)
        ->where('projects.0.jiraUrl', null));
});

test('Jiraのキーは大文字に寄せて保存される', function () {
    $this->post('/projects/create', ['name' => '案件', 'jira_key' => 'nova-42'])
        ->assertRedirect('/projects');

    expect(Project::first()->jira_key)->toBe('NOVA-42');
});

test('形式が不正なJiraのキーは保存できない', function () {
    $this->post('/projects/create', ['name' => '案件', 'jira_key' => 'NOVA/42']);

    expect(Project::count())->toBe(0);
});

test('案件の取引先は顧客マスタから選ぶ', function () {
    // 移植前は顧客 ID を手で打ち込ませており、存在しない ID も通っていた。
    $this->post('/projects/create', ['name' => '案件', 'client_id' => 999])
        ->assertSessionHasErrors('client_id');

    expect(Project::count())->toBe(0);
});

test('案件フォームに取引先の選択肢が渡る', function () {
    createCustomer('選択できる商会');

    $this->get('/projects/create')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Projects/Form')
        ->where('customers.0.name', '選択できる商会'));
});

test('案件の備考を保存できる', function () {
    // description はサーバー側だけが扱い、フォームに入力欄が無かった。
    $this->post('/projects/create', ['name' => '案件', 'description' => '備考のテスト'])
        ->assertRedirect('/projects');

    expect(Project::first()->description)->toBe('備考のテスト');
});

test('集計対象にできない日付カラムは拒否される', function () {
    // 移行前はリクエストの type をそのまま whereYear に渡していた。
    $this->get('/projects/analysis?type=name')
        ->assertRedirect();
});
