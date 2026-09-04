<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Learning\Input\EnrollmentFilter;
use App\Application\Learning\UseCase\CreateEnrollmentUseCase;
use App\Application\Learning\UseCase\DeleteEnrollmentUseCase;
use App\Application\Learning\UseCase\GetEnrollmentUseCase;
use App\Application\Learning\UseCase\ListCoursesUseCase;
use App\Application\Learning\UseCase\ListEnrollmentsUseCase;
use App\Application\Learning\UseCase\UpdateEnrollmentUseCase;
use App\Application\User\UseCase\ListUsersUseCase;
use App\Domain\Learning\Entity\Course;
use App\Domain\Learning\ValueObject\EnrollmentStatus;
use App\Domain\User\Entity\User;
use App\Http\Requests\SaveEnrollmentRequest;
use App\Http\ViewModels\CourseView;
use App\Http\ViewModels\EnrollmentView;
use App\Http\ViewModels\UserView;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * 受講記録。「誰がどの講座をどこまで進めたか」。
 *
 * novalumo/e-learning の records に相当するが、あちらは id と timestamps
 * しか無いテーブルと、settings 画面を丸写ししただけのビューがあるだけで、
 * 受講という概念そのものが未実装だった。ここは実質すべて新規設計。
 */
final class EnrollmentController extends Controller
{
    public function index(
        Request $request,
        ListEnrollmentsUseCase $listEnrollments,
        ListUsersUseCase $listUsers,
        ListCoursesUseCase $listCourses,
    ): InertiaResponse {
        $filter = $this->filterFrom($request);
        $summary = $listEnrollments->execute($filter);
        $courses = $listCourses->execute();

        return Inertia::render('Enrollments/Index', [
            'enrollments' => EnrollmentView::collection(
                $summary->enrollments,
                $this->userNames($listUsers->execute()),
                $this->courseMap($courses),
            ),
            'summary' => [
                'completedCount' => $summary->completedCount,
                'inProgressCount' => $summary->inProgressCount,
                'earnedExp' => $summary->earnedExp->value,
                'earnedExpLabel' => $summary->earnedExp->format(),
            ],
            'users' => UserView::options($listUsers->execute()),
            'courses' => CourseView::options($courses),
            'statuses' => $this->statusOptions(),
            'filter' => [
                'userId' => $filter->userId,
                'courseId' => $filter->courseId,
                'status' => $filter->status,
            ],
            'urls' => [
                'self' => route('enrollments.index'),
                'create' => route('enrollments.create'),
                'courses' => route('courses.index'),
            ],
        ]);
    }

    public function create(ListUsersUseCase $listUsers, ListCoursesUseCase $listCourses): InertiaResponse
    {
        return Inertia::render('Enrollments/Form', [
            'enrollment' => null,
            // 日付の既定値はサーバーで決める
            'defaults' => [
                'date' => date('Y-m-d'),
                // ログイン中のユーザーを受講者の初期値にする
                'userId' => session('user_id') === null ? null : (int) session('user_id'),
            ],
            'users' => UserView::options($listUsers->execute()),
            // 下書きの講座は選択肢に出さない
            'courses' => CourseView::options($listCourses->execute(publishedOnly: true)),
            'statuses' => $this->statusOptions(),
            'urls' => [
                'submit' => route('enrollments.create'),
                'back' => route('enrollments.index'),
            ],
        ]);
    }

    public function store(SaveEnrollmentRequest $request, CreateEnrollmentUseCase $createEnrollment): RedirectResponse
    {
        $createEnrollment->execute($request->toInput());

        return redirect()->route('enrollments.index')->with(Flash::success('受講記録を登録しました'));
    }

    public function edit(
        int $id,
        GetEnrollmentUseCase $getEnrollment,
        ListUsersUseCase $listUsers,
        ListCoursesUseCase $listCourses,
    ): InertiaResponse {
        $enrollment = $getEnrollment->execute($id);
        // 編集では下書きに戻した講座も選択肢に残す。既にその講座で記録された
        // 受講を開いたときに、選択が外れて別の講座に化けるのを防ぐため
        $courses = $listCourses->execute();
        $users = $listUsers->execute();

        return Inertia::render('Enrollments/Form', [
            'enrollment' => EnrollmentView::fromEntity(
                $enrollment,
                $this->userNames($users),
                $this->courseMap($courses),
            ),
            'defaults' => null,
            'users' => UserView::options($users),
            'courses' => CourseView::options($courses),
            'statuses' => $this->statusOptions(),
            'urls' => [
                'submit' => route('enrollments.edit', ['id' => $id]),
                'back' => route('enrollments.index'),
            ],
        ]);
    }

    public function update(
        SaveEnrollmentRequest $request,
        int $id,
        UpdateEnrollmentUseCase $updateEnrollment,
    ): RedirectResponse {
        $updateEnrollment->execute($id, $request->toInput());

        return redirect()->route('enrollments.index')->with(Flash::success('受講記録を更新しました'));
    }

    public function destroy(int $id, DeleteEnrollmentUseCase $deleteEnrollment): RedirectResponse
    {
        $deleteEnrollment->execute($id);

        return redirect()->route('enrollments.index')->with(Flash::success('受講記録を削除しました'));
    }

    private function filterFrom(Request $request): EnrollmentFilter
    {
        return EnrollmentFilter::of(
            $request->input('user_id'),
            $request->input('course_id'),
            $request->input('status'),
        );
    }

    /**
     * 状態の選択肢。ドメインの EnrollmentStatus が唯一の定義。
     *
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            static fn (string $value, string $label): array => ['value' => $value, 'label' => $label],
            array_keys(EnrollmentStatus::options()),
            array_values(EnrollmentStatus::options()),
        );
    }

    /**
     * ユーザー ID => 名前。一覧で記録ごとに引くと N+1 になるためまとめて引く。
     *
     * @param list<User> $users
     * @return array<int, string>
     */
    private function userNames(array $users): array
    {
        $names = [];

        foreach ($users as $user) {
            $names[(int) $user->id()] = $user->name();
        }

        return $names;
    }

    /**
     * 講座 ID => 講座名とポイント。
     *
     * @param list<Course> $courses
     * @return array<int, array{title: string, exp: int}>
     */
    private function courseMap(array $courses): array
    {
        $map = [];

        foreach ($courses as $course) {
            $map[(int) $course->id()] = ['title' => $course->title(), 'exp' => $course->exp()->value];
        }

        return $map;
    }
}
