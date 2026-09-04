<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Learning\Input\SubmissionFilter;
use App\Application\Learning\UseCase\CreateSubmissionUseCase;
use App\Application\Learning\UseCase\DeleteSubmissionUseCase;
use App\Application\Learning\UseCase\GetSubmissionUseCase;
use App\Application\Learning\UseCase\ListAssignmentsUseCase;
use App\Application\Learning\UseCase\ListCoursesUseCase;
use App\Application\Learning\UseCase\ListSubmissionsUseCase;
use App\Application\Learning\UseCase\UpdateSubmissionUseCase;
use App\Application\User\UseCase\ListUsersUseCase;
use App\Domain\Learning\Entity\Assignment;
use App\Domain\Learning\Entity\Course;
use App\Domain\Learning\ValueObject\SubmissionStatus;
use App\Domain\User\Entity\User;
use App\Http\Requests\SaveSubmissionRequest;
use App\Http\ViewModels\AssignmentView;
use App\Http\ViewModels\SubmissionView;
use App\Http\ViewModels\UserView;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * 提出物。「誰がどの課題を出して、どう評価されたか」。
 *
 * 参考にした novalumo/e-learning に該当する実装は無く、ビューの
 * サイドバーに「提出物」という項目名があるだけだった。
 */
final class SubmissionController extends Controller
{
    public function index(
        Request $request,
        ListSubmissionsUseCase $listSubmissions,
        ListAssignmentsUseCase $listAssignments,
        ListCoursesUseCase $listCourses,
        ListUsersUseCase $listUsers,
    ): InertiaResponse {
        $filter = $this->filterFrom($request);
        $assignments = $listAssignments->execute();
        $courseTitles = $this->courseTitles($listCourses->execute());
        $users = $listUsers->execute();

        return Inertia::render('Submissions/Index', [
            'submissions' => SubmissionView::collection(
                $listSubmissions->execute($filter),
                $this->assignmentsById($assignments),
                $courseTitles,
                $this->userNames($users),
            ),
            'assignments' => AssignmentView::options($assignments, $courseTitles),
            'users' => UserView::options($users),
            'statuses' => $this->statusOptions(),
            'filter' => [
                'userId' => $filter->userId,
                'assignmentId' => $filter->assignmentId,
                'status' => $filter->status,
            ],
            'urls' => [
                'self' => route('submissions.index'),
                'create' => route('submissions.create'),
                'assignments' => route('assignments.index'),
            ],
        ]);
    }

    public function create(
        ListAssignmentsUseCase $listAssignments,
        ListCoursesUseCase $listCourses,
        ListUsersUseCase $listUsers,
    ): InertiaResponse {
        return Inertia::render('Submissions/Form', [
            'submission' => null,
            // 日付と提出者の既定値はサーバーで決める
            'defaults' => [
                'date' => date('Y-m-d'),
                'userId' => session('user_id') === null ? null : (int) session('user_id'),
            ],
            'assignments' => AssignmentView::options(
                $listAssignments->execute(),
                $this->courseTitles($listCourses->execute()),
            ),
            'users' => UserView::options($listUsers->execute()),
            'statuses' => $this->statusOptions(),
            'urls' => [
                'submit' => route('submissions.create'),
                'back' => route('submissions.index'),
            ],
        ]);
    }

    public function store(SaveSubmissionRequest $request, CreateSubmissionUseCase $create): RedirectResponse
    {
        $create->execute($request->toInput());

        return redirect()->route('submissions.index')->with(Flash::success('提出物を登録しました'));
    }

    public function edit(
        int $id,
        GetSubmissionUseCase $getSubmission,
        ListAssignmentsUseCase $listAssignments,
        ListCoursesUseCase $listCourses,
        ListUsersUseCase $listUsers,
    ): InertiaResponse {
        $assignments = $listAssignments->execute();
        $courseTitles = $this->courseTitles($listCourses->execute());
        $users = $listUsers->execute();

        return Inertia::render('Submissions/Form', [
            'submission' => SubmissionView::fromEntity(
                $getSubmission->execute($id),
                $this->assignmentsById($assignments),
                $courseTitles,
                $this->userNames($users),
            ),
            'defaults' => null,
            'assignments' => AssignmentView::options($assignments, $courseTitles),
            'users' => UserView::options($users),
            'statuses' => $this->statusOptions(),
            'urls' => [
                'submit' => route('submissions.edit', ['id' => $id]),
                'back' => route('submissions.index'),
            ],
        ]);
    }

    public function update(SaveSubmissionRequest $request, int $id, UpdateSubmissionUseCase $update): RedirectResponse
    {
        $update->execute($id, $request->toInput());

        return redirect()->route('submissions.index')->with(Flash::success('提出物を更新しました'));
    }

    public function destroy(int $id, DeleteSubmissionUseCase $delete): RedirectResponse
    {
        $delete->execute($id);

        return redirect()->route('submissions.index')->with(Flash::success('提出物を削除しました'));
    }

    private function filterFrom(Request $request): SubmissionFilter
    {
        return SubmissionFilter::of(
            $request->input('user_id'),
            $request->input('assignment_id'),
            $request->input('status'),
        );
    }

    /**
     * 状態の選択肢。ドメインの SubmissionStatus が唯一の定義。
     *
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            static fn (string $value, string $label): array => ['value' => $value, 'label' => $label],
            array_keys(SubmissionStatus::options()),
            array_values(SubmissionStatus::options()),
        );
    }

    /**
     * @param list<Assignment> $assignments
     * @return array<int, Assignment>
     */
    private function assignmentsById(array $assignments): array
    {
        $map = [];

        foreach ($assignments as $assignment) {
            $map[(int) $assignment->id()] = $assignment;
        }

        return $map;
    }

    /**
     * @param list<Course> $courses
     * @return array<int, string>
     */
    private function courseTitles(array $courses): array
    {
        $titles = [];

        foreach ($courses as $course) {
            $titles[(int) $course->id()] = $course->title();
        }

        return $titles;
    }

    /**
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
}
