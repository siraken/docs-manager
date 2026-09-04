<?php

use App\Infrastructure\Persistence\Eloquent\Models\Course;
use App\Infrastructure\Persistence\Eloquent\Models\Enrollment;
use Inertia\Testing\AssertableInertia;

/**
 * 社内研修（講座・受講記録）。
 *
 * novalumo/e-learning を参考に新規開発したもの。参考実装は classes テーブル
 * (title + exp) があるだけで、records は id と timestamps しか持たなかった。
 */

beforeEach(function () {
    actingAsUser(createUser());
});

// --- 講座 -----------------------------------------------------------

test('講座の一覧が表示できる', function () {
    createCourse('Laravel 入門', 50);

    $this->get('/courses')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Courses/Index')
        ->has('courses', 1)
        ->where('courses.0.title', 'Laravel 入門')
        ->where('courses.0.exp', 50)
        ->where('courses.0.isPublished', true));
});

test('講座を登録できる', function () {
    $this->post('/courses/create', [
        'title' => 'セキュリティ基礎',
        'description' => '説明',
        'exp' => 30,
        'is_published' => true,
    ])->assertRedirect('/courses');

    $course = Course::first();
    expect($course->title)->toBe('セキュリティ基礎')
        ->and((int) $course->exp)->toBe(30);
});

test('講座名が空だと登録できない', function () {
    $this->post('/courses/create', ['title' => ''])->assertSessionHasErrors('title');

    expect(Course::count())->toBe(0);
});

test('獲得ポイントが負だと登録できない', function () {
    $this->post('/courses/create', ['title' => '講座', 'exp' => -1])
        ->assertSessionHasErrors('exp');

    expect(Course::count())->toBe(0);
});

test('獲得ポイントは省略できる', function () {
    $this->post('/courses/create', ['title' => 'ポイント無しの講座'])->assertRedirect('/courses');

    expect((int) Course::first()->exp)->toBe(0);
});

test('下書きの講座は受講記録の選択肢に出ない', function () {
    createCourse('公開中', 10);
    createCourse('下書き', 10, ['is_published' => false]);

    $this->get('/enrollments/create')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Enrollments/Form')
        ->has('courses', 1)
        ->where('courses.0.name', '公開中'));
});

test('編集では下書きの講座も選択肢に残る', function () {
    // 既にその講座で記録された受講を開いたとき、選択が外れて
    // 別の講座に化けるのを防ぐ
    $course = createCourse('下書きに戻した講座', 10, ['is_published' => false]);
    $user = createUser(['email' => 'learner@example.com']);
    $enrollment = createEnrollment($user->id, $course->id);

    $this->get('/enrollments/edit/' . $enrollment->id)->assertInertia(fn (AssertableInertia $page) => $page
        ->has('courses', 1)
        ->where('enrollment.courseId', $course->id));
});

test('受講記録のある講座は削除できない', function () {
    $course = createCourse();
    $user = createUser(['email' => 'learner@example.com']);
    createEnrollment($user->id, $course->id);

    $this->delete('/courses/delete/' . $course->id);

    expect(Course::find($course->id))->not->toBeNull();

    // DomainException は bootstrap/app.php が拾ってフラッシュに載せる
    $this->get('/courses')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('flash.status', 'danger')
        ->where('flash.message', fn (string $m) => str_contains($m, '削除できません')));
});

test('受講記録の無い講座は削除できる', function () {
    $course = createCourse();

    $this->delete('/courses/delete/' . $course->id)->assertRedirect('/courses');

    expect(Course::count())->toBe(0);
});

// --- 受講記録 -------------------------------------------------------

test('受講記録の一覧が表示できる', function () {
    $course = createCourse('Laravel 入門', 50);
    $user = createUser(['email' => 'learner@example.com', 'name' => '受講太郎']);
    createEnrollment($user->id, $course->id);

    $this->get('/enrollments')->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Enrollments/Index')
        ->has('enrollments', 1)
        ->where('enrollments.0.userName', '受講太郎')
        ->where('enrollments.0.courseTitle', 'Laravel 入門')
        ->where('enrollments.0.status', '完了')
        ->where('enrollments.0.exp', 50));
});

test('受講記録を登録できる', function () {
    $course = createCourse();
    $user = createUser(['email' => 'learner@example.com']);

    $this->post('/enrollments/create', enrollmentPayload($user->id, $course->id))
        ->assertRedirect('/enrollments');

    $enrollment = Enrollment::first();
    expect((int) $enrollment->user_id)->toBe($user->id)
        ->and($enrollment->status)->toBe('completed');
});

test('完了日なしでは完了にできない', function () {
    $course = createCourse();
    $user = createUser(['email' => 'learner@example.com']);

    $this->post('/enrollments/create', enrollmentPayload($user->id, $course->id, ['completed_at' => '']))
        ->assertSessionHasErrors('completed_at');

    expect(Enrollment::count())->toBe(0);
});

test('完了日が開始日より前だと登録できない', function () {
    $course = createCourse();
    $user = createUser(['email' => 'learner@example.com']);

    $this->post('/enrollments/create', enrollmentPayload($user->id, $course->id, [
        'started_at' => '2026-09-30',
        'completed_at' => '2026-09-01',
    ]))->assertSessionHasErrors('completed_at');

    expect(Enrollment::count())->toBe(0);
});

test('未受講で登録すると日付が落ちる', function () {
    // 状態だけ戻して日付が取り残される不整合を作れないようにしている
    $course = createCourse();
    $user = createUser(['email' => 'learner@example.com']);

    $this->post('/enrollments/create', enrollmentPayload($user->id, $course->id, [
        'status' => 'not_started',
    ]))->assertRedirect('/enrollments');

    $enrollment = Enrollment::first();
    expect($enrollment->started_at)->toBeNull()
        ->and($enrollment->completed_at)->toBeNull();
});

test('受講中に戻すと完了日が落ちる', function () {
    $course = createCourse();
    $user = createUser(['email' => 'learner@example.com']);
    $enrollment = createEnrollment($user->id, $course->id);

    $this->post('/enrollments/edit/' . $enrollment->id, enrollmentPayload($user->id, $course->id, [
        'status' => 'in_progress',
    ]))->assertRedirect('/enrollments');

    $updated = Enrollment::find($enrollment->id);
    expect($updated->status)->toBe('in_progress')
        ->and($updated->completed_at)->toBeNull()
        ->and((string) $updated->started_at)->toStartWith('2026-09-01');
});

test('同じ受講者と講座の記録は2つ作れない', function () {
    // 2 行あると完了なのか受講中なのかが決められなくなる
    $course = createCourse();
    $user = createUser(['email' => 'learner@example.com']);
    createEnrollment($user->id, $course->id);

    $this->post('/enrollments/create', enrollmentPayload($user->id, $course->id));

    expect(Enrollment::count())->toBe(1);

    $this->get('/enrollments')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('flash.status', 'danger')
        ->where('flash.message', fn (string $m) => str_contains($m, '既にあります')));
});

test('自分自身の記録は重複扱いにならない', function () {
    $course = createCourse();
    $user = createUser(['email' => 'learner@example.com']);
    $enrollment = createEnrollment($user->id, $course->id);

    $this->post('/enrollments/edit/' . $enrollment->id, enrollmentPayload($user->id, $course->id, [
        'note' => 'メモを足した',
    ]))->assertRedirect('/enrollments');

    expect(Enrollment::find($enrollment->id)->note)->toBe('メモを足した');
});

test('存在しない講座は指定できない', function () {
    $user = createUser(['email' => 'learner@example.com']);

    $this->post('/enrollments/create', enrollmentPayload($user->id, 999))
        ->assertSessionHasErrors('course_id');

    expect(Enrollment::count())->toBe(0);
});

// --- 集計 -----------------------------------------------------------

test('ポイントは完了した受講だけを足す', function () {
    // 受講中のぶんまで数えると「まだ終わっていないのに獲得済み」になる
    $a = createCourse('完了する講座', 50);
    $b = createCourse('受講中の講座', 30);
    $user = createUser(['email' => 'learner@example.com']);

    createEnrollment($user->id, $a->id);
    createEnrollment($user->id, $b->id, ['status' => 'in_progress', 'completed_at' => null]);

    $this->get('/enrollments')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('summary.completedCount', 1)
        ->where('summary.inProgressCount', 1)
        ->where('summary.earnedExp', 50));
});

test('受講中の行はポイントが0で出る', function () {
    $course = createCourse('受講中の講座', 30);
    $user = createUser(['email' => 'learner@example.com']);
    createEnrollment($user->id, $course->id, ['status' => 'in_progress', 'completed_at' => null]);

    $this->get('/enrollments')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('enrollments.0.exp', 0));
});

test('受講者で絞り込める', function () {
    $course = createCourse();
    $a = createUser(['email' => 'a@example.com', 'name' => '受講太郎']);
    $b = createUser(['email' => 'b@example.com', 'name' => '受講花子']);
    createEnrollment($a->id, $course->id);
    createEnrollment($b->id, $course->id);

    $this->get('/enrollments?user_id=' . $a->id)->assertInertia(fn (AssertableInertia $page) => $page
        ->has('enrollments', 1)
        ->where('enrollments.0.userName', '受講太郎'));
});

test('状態で絞り込める', function () {
    $a = createCourse('完了', 10);
    $b = createCourse('受講中', 10);
    $user = createUser(['email' => 'learner@example.com']);
    createEnrollment($user->id, $a->id);
    createEnrollment($user->id, $b->id, ['status' => 'in_progress', 'completed_at' => null]);

    $this->get('/enrollments?status=completed')->assertInertia(fn (AssertableInertia $page) => $page
        ->has('enrollments', 1)
        ->where('enrollments.0.courseTitle', '完了'));
});

// --- 削除とアクセス制御 ---------------------------------------------

test('受講記録を削除できる', function () {
    $course = createCourse();
    $user = createUser(['email' => 'learner@example.com']);
    $enrollment = createEnrollment($user->id, $course->id);

    $this->delete('/enrollments/delete/' . $enrollment->id)->assertRedirect('/enrollments');

    expect(Enrollment::count())->toBe(0);
});

test('存在しない受講記録は404になる', function () {
    $this->get('/enrollments/edit/999')->assertNotFound();
});

test('削除された講座を参照していても一覧は開ける', function () {
    $course = createCourse();
    $user = createUser(['email' => 'learner@example.com']);
    createEnrollment($user->id, $course->id);
    Course::where('id', $course->id)->delete();

    $this->get('/enrollments')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('enrollments.0.courseTitle', '(削除された講座)'));
});

test('未認証では研修にアクセスできない', function () {
    session()->flush();

    $this->get('/enrollments')->assertRedirect('/login');
    $this->get('/courses')->assertRedirect('/login');
});
