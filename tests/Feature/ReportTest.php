<?php

use App\Infrastructure\Persistence\Eloquent\Models\Project;
use App\Infrastructure\Persistence\Eloquent\Models\Report;
use Inertia\Testing\AssertableInertia;

/**
 * 勤務報告 (in-house-timecard-app から移植) のリグレッションテスト。
 *
 * 移植元で壊れていた箇所を中心に見る。
 */

beforeEach(function () {
    actingAsUser(createUser());
});

// --- 一覧と絞り込み -------------------------------------------------

test('一覧が表示できる', function () {
    createReport(['title' => '一覧に出る作業']);

    $this->get('/reports?year=2026&month=9')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Reports/Index')
        ->has('reports', 1)
        ->where('reports.0.title', '一覧に出る作業')
        ->where('reports.0.workTimeLabel', '9:00'));
});

test('年月で絞り込める', function () {
    // 移植元は一覧の年月セレクトが GET で year / month を送るのに、
    // コントローラがルートパラメータで受けており絞り込みが効かなかった。
    createReport(['date' => '2026-09-04', 'title' => '9月の作業']);
    createReport(['date' => '2026-10-01', 'title' => '10月の作業']);

    $this->get('/reports?year=2026&month=9')->assertInertia(fn (AssertableInertia $page) => $page
        ->has('reports', 1)
        ->where('reports.0.title', '9月の作業'));
});

test('件名で絞り込める', function () {
    createReport(['title' => '設計レビュー']);
    createReport(['title' => '実装']);

    $this->get('/reports?year=2026&month=9&q=' . urlencode('レビュー'))->assertInertia(fn (AssertableInertia $page) => $page
        ->has('reports', 1)
        ->where('reports.0.title', '設計レビュー'));
});

test('LIKE のワイルドカードは文字として扱う', function () {
    createReport(['title' => '実装']);

    // エスケープしていないと % が全件一致になる
    $this->get('/reports?year=2026&month=9&q=%')->assertInertia(fn (AssertableInertia $page) => $page
        ->has('reports', 0));
});

test('年月の指定が無ければ当月に絞り込む', function () {
    $thisMonth = new DateTimeImmutable('first day of this month');

    createReport(['date' => $thisMonth->format('Y-m-d'), 'title' => '今月の作業']);
    createReport(['date' => $thisMonth->modify('-2 months')->format('Y-m-d'), 'title' => '2か月前の作業']);

    $this->get('/reports')->assertInertia(fn (AssertableInertia $page) => $page
        ->has('reports', 1)
        ->where('reports.0.title', '今月の作業')
        ->where('filter.year', (int) $thisMonth->format('Y'))
        ->where('filter.month', (int) $thisMonth->format('n')));
});

// --- 集計 -----------------------------------------------------------

test('集計は絞り込み結果の全件を対象にする', function () {
    // 移植元はビューの中でページ内の行だけを足していたため、
    // 2 ページ目以降が「総」勤務時間に入らなかった。
    foreach (range(1, 12) as $day) {
        createReport(['date' => sprintf('2026-09-%02d', $day), 'work_minutes' => 60]);
    }

    $this->get('/reports?year=2026&month=9')->assertInertia(fn (AssertableInertia $page) => $page
        ->has('reports', 12)
        ->where('summary.totalWorkMinutes', 720)
        ->where('summary.totalWorkTimeLabel', '12:00'));
});

test('総勤務日数が数えられる', function () {
    // 移植元は $reports->sum('work_days') を呼んでいたが work_days という
    // カラムは存在せず、総勤務日数は常に 0 だった。
    createReport(['date' => '2026-09-01', 'work_minutes' => 60]);
    createReport(['date' => '2026-09-01', 'work_minutes' => 120]); // 同じ日は 1 日
    createReport(['date' => '2026-09-02', 'work_minutes' => 60]);

    $this->get('/reports?year=2026&month=9')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('summary.workDays', 2)
        ->where('summary.totalWorkMinutes', 240));
});

test('年の選択肢に当年が含まれる', function () {
    // 移植元はビューに 2020〜2024 を直書きしており、2025 年以降を選べなかった。
    $this->get('/reports')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('years', range(2020, (int) date('Y') + 1)));
});

// --- 登録 -----------------------------------------------------------

test('勤務報告を登録できる', function () {
    $this->post('/reports/create', reportPayload())->assertRedirect('/reports');

    $report = Report::first();
    expect($report)->not->toBeNull()
        ->and($report->title)->toBe('実装作業')
        ->and((int) $report->work_minutes)->toBe(540);
});

test('勤務時間は申告値ではなく始業と終業から計算される', function () {
    // 移植元は work_time を手入力させたうえ始業・終業も別に入力させており、
    // 両者が食い違っても誰も気付けなかった。ここでは 3 時間と申告しているが、
    // 10:00-15:30 なので 5 時間 30 分 (330 分) が保存される。
    $this->post('/reports/create', reportPayload([
        'start_time' => '10:00',
        'end_time' => '15:30',
        'work_time' => '3', // 申告値は使われない
    ]))->assertRedirect('/reports');

    expect((int) Report::first()->work_minutes)->toBe(330);
});

test('時刻が片方だけなら申告した勤務時間が使われる', function () {
    $this->post('/reports/create', reportPayload([
        'start_time' => '10:00',
        'end_time' => '',
        'work_time' => '4.5',
    ]))->assertRedirect('/reports');

    expect((int) Report::first()->work_minutes)->toBe(270);
});

test('日を跨ぐ勤務でも負の勤務時間にならない', function () {
    $this->post('/reports/create', reportPayload([
        'start_time' => '22:00',
        'end_time' => '02:00',
        'work_time' => '',
    ]))->assertRedirect('/reports');

    expect((int) Report::first()->work_minutes)->toBe(240);
});

test('件名が空だと登録できない', function () {
    // 移植元は $request->all() を検証なしで fill() していた。
    $this->post('/reports/create', reportPayload(['title' => '']))
        ->assertSessionHasErrors('title');

    expect(Report::count())->toBe(0);
});

test('勤務日が空だと登録できない', function () {
    $this->post('/reports/create', reportPayload(['date' => '']))
        ->assertSessionHasErrors('date');

    expect(Report::count())->toBe(0);
});

test('存在しない案件は指定できない', function () {
    $this->post('/reports/create', reportPayload(['project_id' => 999]))
        ->assertSessionHasErrors('project_id');

    expect(Report::count())->toBe(0);
});

test('担当者と取引先と案件が id で保存される', function () {
    // 移植元のフォームは担当者・取引先のセレクトが名前の文字列を送っており、
    // 案件のセレクトは name 属性が空で送信すらされていなかった。
    $user = createUser(['email' => 'worker@example.com', 'name' => '作業太郎']);
    $customer = createCustomer('取引先商会');
    $project = Project::create(['name' => '移植プロジェクト']);

    $this->post('/reports/create', reportPayload([
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'project_id' => $project->id,
    ]))->assertRedirect('/reports');

    $report = Report::first();
    expect((int) $report->user_id)->toBe($user->id)
        ->and((int) $report->customer_id)->toBe($customer->id)
        ->and((int) $report->project_id)->toBe($project->id);
});

test('一覧には id ではなく名前が渡る', function () {
    // 移植元の一覧は $report['who'] という存在しないキーを引いていて空欄だった。
    $user = createUser(['email' => 'worker@example.com', 'name' => '作業太郎']);
    $project = Project::create(['name' => '移植プロジェクト']);
    createReport(['user_id' => $user->id, 'project_id' => $project->id]);

    $this->get('/reports?year=2026&month=9')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('reports.0.userName', '作業太郎')
        ->where('reports.0.projectName', '移植プロジェクト'));
});

// --- フォーム -------------------------------------------------------

test('新規フォームの既定値はサーバーが決める', function () {
    // 画面が new Date() を持つとサーバーの時計とずれ、テストから固定できない。
    $this->get('/reports/create')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Reports/Form')
        ->where('report', null)
        ->where('defaults.date', date('Y-m-d'))
        ->has('users')
        ->has('customers')
        ->has('projects'));
});

test('編集フォームには既存の値が渡る', function () {
    $report = createReport(['title' => '編集対象']);

    $this->get('/reports/edit/' . $report->id)->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Reports/Form')
        ->where('report.title', '編集対象')
        ->where('report.startTime', '09:00')
        ->where('report.endTime', '18:00')
        ->where('report.workHours', 9));
});

test('勤務報告を更新できる', function () {
    $report = createReport(['title' => '変更前']);

    $this->post('/reports/edit/' . $report->id, reportPayload([
        'title' => '変更後',
        'start_time' => '13:00',
        'end_time' => '17:00',
    ]))->assertRedirect('/reports');

    $updated = Report::find($report->id);
    expect($updated->title)->toBe('変更後')
        ->and((int) $updated->work_minutes)->toBe(240)
        // 更新で id が変わらないこと
        ->and(Report::count())->toBe(1);
});

// --- 詳細と削除 -----------------------------------------------------

test('詳細が表示できる', function () {
    // 移植元は reports/show.blade.php の中身が空で、開いても何も出なかった。
    $report = createReport(['title' => '詳細対象']);

    $this->get('/reports/view/' . $report->id)->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Reports/Show')
        ->where('report.title', '詳細対象')
        ->where('report.workTimeLabel', '9:00'));
});

test('存在しない勤務報告は404になる', function () {
    $this->get('/reports/view/999')->assertNotFound();
});

test('勤務報告を削除できる', function () {
    // 移植元はルートが destroy を指しているのにメソッド名が delete で、
    // 削除しようとすると必ず 500 になっていた。
    $report = createReport();

    $this->delete('/reports/delete/' . $report->id)->assertRedirect('/reports');

    expect(Report::count())->toBe(0);
});

test('未認証では勤務報告にアクセスできない', function () {
    session()->flush();

    $this->get('/reports')->assertRedirect('/login');
});
