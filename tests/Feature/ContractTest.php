<?php

use App\Infrastructure\Persistence\Eloquent\Models\Contract;
use Inertia\Testing\AssertableInertia;

/**
 * 契約管理 (in-house-timecard-app から移植) のリグレッションテスト。
 */

beforeEach(function () {
    actingAsUser(createUser());
});

// --- 一覧 -----------------------------------------------------------

test('一覧が表示できる', function () {
    createContract(['name' => '一覧に出る契約']);

    $this->get('/contracts')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Contracts/Index')
        ->has('contracts', 1)
        ->where('contracts.0.name', '一覧に出る契約')
        ->where('contracts.0.contractNo', 'CT-001'));
});

test('一覧には取引先名が渡る', function () {
    // 移植元の一覧は列見出しが「取引先」なのに、存在しないカラム
    // ($contract['pid']) を引いて Jira のリンクを組んでいた。
    $customer = createCustomer('取引先商会');
    createContract(['customer_id' => $customer->id]);

    $this->get('/contracts')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('contracts.0.customerName', '取引先商会'));
});

test('契約期間が開始日と終了日の両方で表示される', function () {
    // 移植元は列見出しが「契約期間」なのに開始日しか出していなかった。
    createContract(['start_date' => '2026-09-01', 'end_date' => '2027-08-31']);

    $this->get('/contracts')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('contracts.0.termLabel', '2026/09/01 〜 2027/08/31'));
});

test('状態が契約期間から導出される', function () {
    createContract(['name' => '期間内', 'start_date' => '2020-01-01', 'end_date' => '2099-12-31']);
    createContract(['name' => '開始前', 'start_date' => '2099-01-01', 'end_date' => '2099-12-31']);
    createContract(['name' => '終了済', 'start_date' => '2000-01-01', 'end_date' => '2000-12-31']);

    $this->get('/contracts')->assertInertia(function (AssertableInertia $page): void {
        $contracts = collect($page->toArray()['props']['contracts'])->keyBy('name');

        expect($contracts['期間内']['status'])->toBe('契約中')
            ->and($contracts['開始前']['status'])->toBe('開始前')
            ->and($contracts['終了済']['status'])->toBe('終了');
    });
});

// --- 登録 -----------------------------------------------------------

test('契約を登録できる', function () {
    $this->post('/contracts/create', contractPayload())->assertRedirect('/contracts');

    $contract = Contract::first();
    expect($contract)->not->toBeNull()
        ->and($contract->name)->toBe('保守契約')
        // 移植元のフォームは pid を送っていたが、テーブルに無いカラムだったため
        // 契約番号はどこからも入力できなかった
        ->and($contract->contract_no)->toBe('CT-001');
});

test('取引先を紐付けて登録できる', function () {
    $customer = createCustomer('取引先商会');

    $this->post('/contracts/create', contractPayload(['customer_id' => $customer->id]))
        ->assertRedirect('/contracts');

    expect((int) Contract::first()->customer_id)->toBe($customer->id);
});

test('契約名が空だと登録できない', function () {
    // 移植元は $request->all() を検証なしで fill() していた。
    $this->post('/contracts/create', contractPayload(['name' => '']))
        ->assertSessionHasErrors('name');

    expect(Contract::count())->toBe(0);
});

test('終了日が開始日より前だと登録できない', function () {
    $this->post('/contracts/create', contractPayload([
        'start_date' => '2026-09-30',
        'end_date' => '2026-09-01',
    ]))->assertSessionHasErrors('end_date');

    expect(Contract::count())->toBe(0);
});

test('存在しない取引先は指定できない', function () {
    $this->post('/contracts/create', contractPayload(['customer_id' => 999]))
        ->assertSessionHasErrors('customer_id');

    expect(Contract::count())->toBe(0);
});

test('契約期間は省略できる', function () {
    $this->post('/contracts/create', contractPayload(['start_date' => '', 'end_date' => '']))
        ->assertRedirect('/contracts');

    $contract = Contract::first();
    expect($contract->start_date)->toBeNull()
        ->and($contract->end_date)->toBeNull();

    $this->get('/contracts')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('contracts.0.termLabel', '-')
        ->where('contracts.0.status', '契約中'));
});

// --- フォーム -------------------------------------------------------

test('新規フォームが表示できる', function () {
    $this->get('/contracts/create')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Contracts/Form')
        ->where('contract', null)
        ->has('customers'));
});

test('編集フォームには既存の値が渡る', function () {
    // 移植元の編集画面はコントローラが渡さない変数 ($name / $pid など) を
    // 参照し、form の action も id 抜きで route('contracts.update') を
    // 組もうとしていたため、開いた時点で 500 になっていた。
    $contract = createContract(['name' => '編集対象']);

    $this->get('/contracts/edit/' . $contract->id)->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Contracts/Form')
        ->where('contract.name', '編集対象')
        ->where('contract.contractNo', 'CT-001')
        ->where('contract.startDate', '2026-09-01')
        ->where('contract.urls.edit', url('/contracts/edit/' . $contract->id)));
});

test('契約を更新できる', function () {
    $contract = createContract(['name' => '変更前']);

    $this->post('/contracts/edit/' . $contract->id, contractPayload(['name' => '変更後']))
        ->assertRedirect('/contracts');

    $updated = Contract::find($contract->id);
    expect($updated->name)->toBe('変更後')
        // 更新で id が変わらないこと
        ->and(Contract::count())->toBe(1);
});

test('存在しない契約は404になる', function () {
    $this->get('/contracts/edit/999')->assertNotFound();
});

// --- 削除 -----------------------------------------------------------

test('契約を削除できる', function () {
    // 移植元は編集画面に type="button" の削除ボタンがあるだけで何も起きず、
    // サーバー側の受け口も無かった。
    $contract = createContract();

    $this->delete('/contracts/delete/' . $contract->id)->assertRedirect('/contracts');

    expect(Contract::count())->toBe(0);
});

test('存在しない契約は削除できない', function () {
    $this->delete('/contracts/delete/999')->assertNotFound();
});

test('未認証では契約にアクセスできない', function () {
    session()->flush();

    $this->get('/contracts')->assertRedirect('/login');
});
