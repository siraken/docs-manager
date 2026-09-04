<?php

use App\Infrastructure\Persistence\Eloquent\Models\Assignment;
use App\Infrastructure\Persistence\Eloquent\Models\Course;
use App\Infrastructure\Persistence\Eloquent\Models\Submission;
use Inertia\Testing\AssertableInertia;

/**
 * 課題と提出物。
 *
 * novalumo/e-learning の tasks テーブルは id と timestamps しか持たず、
 * コントローラは view を返すだけ、ビューは settings 画面の丸写しだった。
 * 手掛かりはサイドバーの「提出物」という項目名だけで、設計は実質すべて新規。
 */

beforeEach(function () {
    actingAsUser(createUser());
    $this->course = createCourse('Laravel 入門', 50);
});

// --- 課題 -----------------------------------------------------------

test('課題の一覧が表示できる', function () {
    createAssignment($this->course->id, ['title' => '演習 1']);

    $this->get('/assignments')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Assignments/Index')
        ->has('assignments', 1)
        ->where('assignments.0.title', '演習 1')
        ->where('assignments.0.courseTitle', 'Laravel 入門'));
});

test('課題を登録できる', function () {
    $this->post('/assignments/create', [
        'course_id' => $this->course->id,
        'title' => '演習 2',
        'description' => 'ルーティングを書く',
        'due_on' => '2026-10-31',
    ])->assertRedirect('/assignments');

    $assignment = Assignment::first();
    expect($assignment->title)->toBe('演習 2')
        ->and((int) $assignment->course_id)->toBe($this->course->id);
});

test('課題名が空だと登録できない', function () {
    $this->post('/assignments/create', ['course_id' => $this->course->id, 'title' => ''])
        ->assertSessionHasErrors('title');

    expect(Assignment::count())->toBe(0);
});

test('存在しない講座は指定できない', function () {
    $this->post('/assignments/create', ['course_id' => 999, 'title' => '演習'])
        ->assertSessionHasErrors('course_id');

    expect(Assignment::count())->toBe(0);
});

test('提出期限は省略できる', function () {
    $this->post('/assignments/create', ['course_id' => $this->course->id, 'title' => '期限なしの課題'])
        ->assertRedirect('/assignments');

    expect(Assignment::first()->due_on)->toBeNull();

    $this->get('/assignments')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('assignments.0.dueOnLabel', '期限なし')
        ->where('assignments.0.isOverdue', false));
});

test('期限を過ぎた課題は印が付く', function () {
    createAssignment($this->course->id, ['due_on' => '2020-01-01']);

    $this->get('/assignments')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('assignments.0.isOverdue', true));
});

test('下書きの講座には課題を付けられない', function () {
    createCourse('下書きの講座', 10, ['is_published' => false]);

    $this->get('/assignments/create')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Assignments/Form')
        ->has('courses', 1)
        ->where('courses.0.name', 'Laravel 入門'));
});

test('編集では下書きの講座も選択肢に残る', function () {
    $this->course->update(['is_published' => false]);
    $assignment = createAssignment($this->course->id);

    $this->get('/assignments/edit/' . $assignment->id)->assertInertia(fn (AssertableInertia $page) => $page
        ->has('courses', 1)
        ->where('assignment.courseId', $this->course->id));
});

test('提出物のある課題は削除できない', function () {
    $assignment = createAssignment($this->course->id);
    $user = createUser(['email' => 'learner@example.com']);
    createSubmission($assignment->id, $user->id);

    $this->delete('/assignments/delete/' . $assignment->id);

    expect(Assignment::find($assignment->id))->not->toBeNull();

    $this->get('/assignments')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('flash.status', 'danger')
        ->where('flash.message', fn (string $m) => str_contains($m, '削除できません')));
});

test('提出物の無い課題は削除できる', function () {
    $assignment = createAssignment($this->course->id);

    $this->delete('/assignments/delete/' . $assignment->id)->assertRedirect('/assignments');

    expect(Assignment::count())->toBe(0);
});

test('削除された講座の課題でも一覧は開ける', function () {
    createAssignment($this->course->id);
    Course::where('id', $this->course->id)->delete();

    $this->get('/assignments')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('assignments.0.courseTitle', '(削除された講座)'));
});

// --- 提出物 ---------------------------------------------------------

test('提出物の一覧が表示できる', function () {
    $assignment = createAssignment($this->course->id, ['title' => '演習 1']);
    $user = createUser(['email' => 'learner@example.com', 'name' => '受講太郎']);
    createSubmission($assignment->id, $user->id);

    $this->get('/submissions')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Submissions/Index')
        ->has('submissions', 1)
        ->where('submissions.0.userName', '受講太郎')
        ->where('submissions.0.assignmentTitle', '演習 1')
        ->where('submissions.0.courseTitle', 'Laravel 入門')
        ->where('submissions.0.status', '提出済み'));
});

test('提出物を登録できる', function () {
    $assignment = createAssignment($this->course->id);
    $user = createUser(['email' => 'learner@example.com']);

    $this->post('/submissions/create', submissionPayload($assignment->id, $user->id))
        ->assertRedirect('/submissions');

    $submission = Submission::first();
    expect((int) $submission->user_id)->toBe($user->id)
        ->and($submission->status)->toBe('submitted');
});

test('提出日なしでは提出済みにできない', function () {
    $assignment = createAssignment($this->course->id);
    $user = createUser(['email' => 'learner@example.com']);

    $this->post('/submissions/create', submissionPayload($assignment->id, $user->id, ['submitted_at' => '']))
        ->assertSessionHasErrors('submitted_at');

    expect(Submission::count())->toBe(0);
});

test('未提出で登録すると提出日と講評が落ちる', function () {
    $assignment = createAssignment($this->course->id);
    $user = createUser(['email' => 'learner@example.com']);

    $this->post('/submissions/create', submissionPayload($assignment->id, $user->id, [
        'status' => 'not_submitted',
        'submitted_at' => '',
        'feedback' => '講評',
    ]))->assertRedirect('/submissions');

    $submission = Submission::first();
    expect($submission->submitted_at)->toBeNull()
        ->and($submission->feedback)->toBeNull();
});

test('提出済みでは講評が落ちる', function () {
    // 提出されただけの段階でまだ評価はしていない
    $assignment = createAssignment($this->course->id);
    $user = createUser(['email' => 'learner@example.com']);

    $this->post('/submissions/create', submissionPayload($assignment->id, $user->id, [
        'feedback' => 'まだ見ていないのに講評',
    ]))->assertRedirect('/submissions');

    expect(Submission::first()->feedback)->toBeNull();
});

test('差し戻しでは講評を保存できる', function () {
    $assignment = createAssignment($this->course->id);
    $user = createUser(['email' => 'learner@example.com']);

    $this->post('/submissions/create', submissionPayload($assignment->id, $user->id, [
        'status' => 'returned',
        'feedback' => 'ここを直してください',
    ]))->assertRedirect('/submissions');

    expect(Submission::first()->feedback)->toBe('ここを直してください');
});

test('期限に遅れた提出には印が付く', function () {
    $assignment = createAssignment($this->course->id, ['due_on' => '2026-09-10']);
    $user = createUser(['email' => 'learner@example.com']);
    createSubmission($assignment->id, $user->id, ['submitted_at' => '2026-09-20']);

    $this->get('/submissions')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('submissions.0.isLate', true));
});

test('期限内の提出には印が付かない', function () {
    $assignment = createAssignment($this->course->id, ['due_on' => '2026-09-30']);
    $user = createUser(['email' => 'learner@example.com']);
    createSubmission($assignment->id, $user->id, ['submitted_at' => '2026-09-20']);

    $this->get('/submissions')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('submissions.0.isLate', false));
});

test('同じ提出者と課題の提出物は2つ作れない', function () {
    $assignment = createAssignment($this->course->id);
    $user = createUser(['email' => 'learner@example.com']);
    createSubmission($assignment->id, $user->id);

    $this->post('/submissions/create', submissionPayload($assignment->id, $user->id));

    expect(Submission::count())->toBe(1);

    $this->get('/submissions')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('flash.status', 'danger')
        ->where('flash.message', fn (string $m) => str_contains($m, '既にあります')));
});

test('自分自身の提出物は重複扱いにならない', function () {
    $assignment = createAssignment($this->course->id);
    $user = createUser(['email' => 'learner@example.com']);
    $submission = createSubmission($assignment->id, $user->id);

    $this->post('/submissions/edit/' . $submission->id, submissionPayload($assignment->id, $user->id, [
        'status' => 'approved',
        'feedback' => '合格です',
    ]))->assertRedirect('/submissions');

    $updated = Submission::find($submission->id);
    expect($updated->status)->toBe('approved')
        ->and($updated->feedback)->toBe('合格です');
});

test('提出者で絞り込める', function () {
    $assignment = createAssignment($this->course->id);
    $a = createUser(['email' => 'a@example.com', 'name' => '提出太郎']);
    $b = createUser(['email' => 'b@example.com', 'name' => '提出花子']);
    createSubmission($assignment->id, $a->id);
    createSubmission($assignment->id, $b->id);

    $this->get('/submissions?user_id=' . $a->id)->assertInertia(fn (AssertableInertia $page) => $page
        ->has('submissions', 1)
        ->where('submissions.0.userName', '提出太郎'));
});

test('状態で絞り込める', function () {
    $a = createAssignment($this->course->id, ['title' => '合格した課題']);
    $b = createAssignment($this->course->id, ['title' => '提出しただけの課題']);
    $user = createUser(['email' => 'learner@example.com']);
    createSubmission($a->id, $user->id, ['status' => 'approved', 'feedback' => '合格']);
    createSubmission($b->id, $user->id);

    $this->get('/submissions?status=approved')->assertInertia(fn (AssertableInertia $page) => $page
        ->has('submissions', 1)
        ->where('submissions.0.assignmentTitle', '合格した課題'));
});

test('提出物を削除できる', function () {
    $assignment = createAssignment($this->course->id);
    $user = createUser(['email' => 'learner@example.com']);
    $submission = createSubmission($assignment->id, $user->id);

    $this->delete('/submissions/delete/' . $submission->id)->assertRedirect('/submissions');

    expect(Submission::count())->toBe(0);
});

test('存在しない提出物は404になる', function () {
    $this->get('/submissions/edit/999')->assertNotFound();
});

test('削除された課題を参照していても一覧は開ける', function () {
    $assignment = createAssignment($this->course->id);
    $user = createUser(['email' => 'learner@example.com']);
    createSubmission($assignment->id, $user->id);
    Assignment::where('id', $assignment->id)->delete();

    $this->get('/submissions')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('submissions.0.assignmentTitle', '(削除された課題)')
        // 課題が消えていれば遅延の判定はできない
        ->where('submissions.0.isLate', false));
});

test('未認証では課題と提出物にアクセスできない', function () {
    session()->flush();

    $this->get('/assignments')->assertRedirect('/login');
    $this->get('/submissions')->assertRedirect('/login');
});
