<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Learning\UseCase\CreateAssignmentUseCase;
use App\Application\Learning\UseCase\DeleteAssignmentUseCase;
use App\Application\Learning\UseCase\GetAssignmentUseCase;
use App\Application\Learning\UseCase\ListAssignmentsUseCase;
use App\Application\Learning\UseCase\ListCoursesUseCase;
use App\Application\Learning\UseCase\UpdateAssignmentUseCase;
use App\Domain\Learning\Entity\Course;
use App\Http\Requests\SaveAssignmentRequest;
use App\Http\ViewModels\AssignmentView;
use App\Http\ViewModels\CourseView;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * 課題。講座に紐づく提出物の題目。
 *
 * novalumo/e-learning の tasks は id と timestamps しか無いテーブルと、
 * view を返すだけのコントローラ、settings 画面を丸写ししたビューがあるだけ
 * だった。設計は実質すべて新規。
 */
final class AssignmentController extends Controller
{
    public function index(ListAssignmentsUseCase $listAssignments, ListCoursesUseCase $listCourses): InertiaResponse
    {
        return Inertia::render('Assignments/Index', [
            'assignments' => AssignmentView::collection(
                $listAssignments->execute(),
                $this->courseTitles($listCourses->execute()),
            ),
            'urls' => [
                'create' => route('assignments.create'),
                'submissions' => route('submissions.index'),
            ],
        ]);
    }

    public function create(ListCoursesUseCase $listCourses): InertiaResponse
    {
        return Inertia::render('Assignments/Form', [
            'assignment' => null,
            // 下書きの講座には課題を付けられないようにする
            'courses' => CourseView::options($listCourses->execute(publishedOnly: true)),
            'urls' => [
                'submit' => route('assignments.create'),
                'back' => route('assignments.index'),
            ],
        ]);
    }

    public function store(SaveAssignmentRequest $request, CreateAssignmentUseCase $create): RedirectResponse
    {
        $create->execute($request->toInput());

        return redirect()->route('assignments.index')->with(Flash::success('課題を登録しました'));
    }

    public function edit(
        int $id,
        GetAssignmentUseCase $getAssignment,
        ListCoursesUseCase $listCourses,
    ): InertiaResponse {
        // 編集では下書きの講座も残す。既にその講座に付いた課題を開いたときに
        // 選択が外れて別の講座に化けるのを防ぐ
        $courses = $listCourses->execute();

        return Inertia::render('Assignments/Form', [
            'assignment' => AssignmentView::fromEntity(
                $getAssignment->execute($id),
                $this->courseTitles($courses),
            ),
            'courses' => CourseView::options($courses),
            'urls' => [
                'submit' => route('assignments.edit', ['id' => $id]),
                'back' => route('assignments.index'),
            ],
        ]);
    }

    public function update(SaveAssignmentRequest $request, int $id, UpdateAssignmentUseCase $update): RedirectResponse
    {
        $update->execute($id, $request->toInput());

        return redirect()->route('assignments.index')->with(Flash::success('課題を更新しました'));
    }

    public function destroy(int $id, DeleteAssignmentUseCase $delete): RedirectResponse
    {
        $delete->execute($id);

        return redirect()->route('assignments.index')->with(Flash::success('課題を削除しました'));
    }

    /**
     * 講座 ID => 講座名。課題ごとに引くと N+1 になるためまとめて引く。
     *
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
}
