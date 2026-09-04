<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Learning\UseCase\CreateCourseUseCase;
use App\Application\Learning\UseCase\DeleteCourseUseCase;
use App\Application\Learning\UseCase\GetCourseUseCase;
use App\Application\Learning\UseCase\ListCoursesUseCase;
use App\Application\Learning\UseCase\UpdateCourseUseCase;
use App\Http\Requests\SaveCourseRequest;
use App\Http\ViewModels\CourseView;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * 講座 (社内研修の教材)。受講記録が講座を選ぶ元になる。
 *
 * novalumo/e-learning の classes テーブルを参考にしているが、あちらは
 * テーブルがあるだけでモデルもコントローラもビューも無かった。
 */
final class CourseController extends Controller
{
    public function index(ListCoursesUseCase $listCourses): InertiaResponse
    {
        return Inertia::render('Courses/Index', [
            'courses' => CourseView::collection($listCourses->execute()),
            'urls' => [
                'create' => route('courses.create'),
                'enrollments' => route('enrollments.index'),
            ],
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Courses/Form', [
            'course' => null,
            'urls' => [
                'submit' => route('courses.create'),
                'back' => route('courses.index'),
            ],
        ]);
    }

    public function store(SaveCourseRequest $request, CreateCourseUseCase $createCourse): RedirectResponse
    {
        $createCourse->execute($request->toInput());

        return redirect()->route('courses.index')->with(Flash::success('講座を登録しました'));
    }

    public function edit(int $id, GetCourseUseCase $getCourse): InertiaResponse
    {
        return Inertia::render('Courses/Form', [
            'course' => CourseView::fromEntity($getCourse->execute($id)),
            'urls' => [
                'submit' => route('courses.edit', ['id' => $id]),
                'back' => route('courses.index'),
            ],
        ]);
    }

    public function update(SaveCourseRequest $request, int $id, UpdateCourseUseCase $updateCourse): RedirectResponse
    {
        $updateCourse->execute($id, $request->toInput());

        return redirect()->route('courses.index')->with(Flash::success('講座を更新しました'));
    }

    /**
     * 削除。受講記録のある講座は CourseInUseException になり、
     * bootstrap/app.php が元の画面へ戻してメッセージを出す。
     */
    public function destroy(int $id, DeleteCourseUseCase $deleteCourse): RedirectResponse
    {
        $deleteCourse->execute($id);

        return redirect()->route('courses.index')->with(Flash::success('講座を削除しました'));
    }
}
