<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * ユーザー / 顧客 / 案件のリグレッションテスト。
 */
class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    private $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actor = User::create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->withSession([
            'user_id' => $this->actor->id,
            'name' => $this->actor->name,
            'email' => $this->actor->email,
        ]);
    }

    // --- ユーザー -------------------------------------------------------

    public function test_ユーザーを作成するとパスワードがハッシュ化される(): void
    {
        $response = $this->post('/users/create', [
            'name' => '新規ユーザー',
            'email' => 'new@example.com',
            'password' => 'plain-password',
        ]);

        $response->assertRedirect('/users');

        $created = User::where('email', 'new@example.com')->first();
        $this->assertNotNull($created);
        $this->assertNotSame('plain-password', $created->password);
        $this->assertTrue(Hash::check('plain-password', $created->password));
    }

    public function test_パスワード未入力の編集では既存のハッシュが維持される(): void
    {
        $original = User::create([
            'name' => '編集対象',
            'email' => 'edit@example.com',
            'password' => Hash::make('original-password'),
        ]);
        $originalHash = $original->password;

        $this->post('/users/edit/' . $original->id, [
            'name' => '編集後',
            'email' => 'edit@example.com',
            'password' => '',
        ]);

        $updated = User::find($original->id)->makeVisible(['password']);
        $this->assertSame('編集後', $updated->name);
        $this->assertSame($originalHash, $updated->password);
        $this->assertTrue(Hash::check('original-password', $updated->password));
    }

    public function test_パスワードを入力した編集では新しいハッシュになる(): void
    {
        $original = User::create([
            'name' => '編集対象',
            'email' => 'edit@example.com',
            'password' => Hash::make('original-password'),
        ]);

        $this->post('/users/edit/' . $original->id, [
            'name' => '編集対象',
            'email' => 'edit@example.com',
            'password' => 'new-password',
        ]);

        $updated = User::find($original->id)->makeVisible(['password']);
        $this->assertTrue(Hash::check('new-password', $updated->password));
        $this->assertFalse(Hash::check('original-password', $updated->password));
    }

    public function test_NFCとウォレットアドレスは平文のまま保存される(): void
    {
        $user = User::create([
            'name' => '編集対象',
            'email' => 'edit@example.com',
            'password' => Hash::make('pw'),
        ]);

        $this->post('/users/edit/' . $user->id, [
            'name' => '編集対象',
            'email' => 'edit@example.com',
            'password' => '',
            'nfc_serial_number' => 'AA:BB:CC',
            'nfc_pin' => '1234',
            'wallet_address' => '0xabc',
        ]);

        $updated = User::find($user->id)->makeVisible(['nfc_serial_number', 'nfc_pin']);
        // ログイン時に平文比較しているため、保存も平文である必要がある
        $this->assertSame('AA:BB:CC', $updated->nfc_serial_number);
        $this->assertSame('1234', $updated->nfc_pin);
        $this->assertSame('0xabc', $updated->wallet_address);
    }

    public function test_ユーザー一覧が表示できる(): void
    {
        $this->get('/users')->assertOk();
    }

    // --- 顧客 -----------------------------------------------------------

    public function test_顧客を作成できる(): void
    {
        $response = $this->post('/customers/create', [
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
        ]);

        $response->assertRedirect('/customers');
        $customer = Customer::first();
        $this->assertSame('株式会社サンプル', $customer->name);
        $this->assertSame(1, (int) $customer->is_company);
    }

    public function test_顧客編集はPOSTしても保存されない(): void
    {
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
        $this->assertSame('変更前', Customer::find($customer->id)->name);
    }

    public function test_顧客一覧が表示できる(): void
    {
        $this->get('/customers')->assertOk();
    }

    // --- 案件 -----------------------------------------------------------

    public function test_案件を作成できる(): void
    {
        $response = $this->post('/projects/create', [
            'name' => 'テスト案件',
            'description' => '説明',
            'client_id' => 1,
            'related_task_id' => null,
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-31',
            'payment_date' => '2027-01-31',
            'price' => 500000,
            'status' => 1,
        ]);

        $response->assertRedirect();
        $project = Project::first();
        $this->assertNotNull($project);
        $this->assertSame('テスト案件', $project->name);
        $this->assertSame(500000, (int) $project->price);
    }

    public function test_案件一覧のステータス表示は壊れている(): void
    {
        // ProjectController::index() は
        //   switch ($project->status) { case $project->status === 0: ... }
        // と書かれており、case に真偽値が並んでいる (switch (true) の誤用)。
        // さらに sqlite は integer カラムを string で返すため、
        // '1' == false / '2' == false がいずれも偽になり 1 と 2 が default に落ちる。
        // 結果として status 1・2 が「未知」と表示される。
        // DB ドライバで値の型が変わると結果も変わりうる点に注意。
        // 修正するまでの現状の挙動として固定しておく。
        foreach ([0, 1, 2] as $status) {
            $p = new Project();
            $p->name = '案件' . $status;
            $p->status = $status;
            $p->save();
        }

        $response = $this->get('/projects');
        $response->assertOk();

        $projects = $response->viewData('projects')->keyBy('name');
        $this->assertSame('未着手', $projects['案件0']->status);
        $this->assertSame('未知', $projects['案件1']->status);
        $this->assertSame('未知', $projects['案件2']->status);
    }
}
