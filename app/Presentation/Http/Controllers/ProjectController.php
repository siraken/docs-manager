<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\Project\Input\SalesAnalysisCriteria;
use App\Application\Project\UseCase\AnalyzeProjectSalesUseCase;
use App\Application\Project\UseCase\CreateProjectUseCase;
use App\Application\Project\UseCase\GetProjectUseCase;
use App\Application\Project\UseCase\ListProjectsUseCase;
use App\Application\Project\UseCase\UpdateProjectUseCase;
use App\Domain\Project\ValueObject\ProjectStatus;
use App\Presentation\Http\Requests\SaveProjectRequest;
use App\Presentation\Http\Support\Flash;
use App\Presentation\Http\ViewModels\ProjectView;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class ProjectController extends Controller
{
    public function index(ListProjectsUseCase $listProjects): View
    {
        return view('projects.index', [
            'projects' => ProjectView::collection($listProjects->execute()),
        ]);
    }

    public function create(): View
    {
        return view('projects.form', [
            'project' => ProjectView::empty(),
            'statuses' => ProjectStatus::options(),
            'isNew' => true,
        ]);
    }

    public function store(SaveProjectRequest $request, CreateProjectUseCase $createProject): RedirectResponse
    {
        $createProject->execute($request->toInput());

        return redirect()->route('projects.index')->with(Flash::success('案件を登録しました'));
    }

    public function edit(int $id, GetProjectUseCase $getProject): View
    {
        return view('projects.form', [
            'project' => ProjectView::fromEntity($getProject->execute($id)),
            'statuses' => ProjectStatus::options(),
            'isNew' => false,
        ]);
    }

    public function update(SaveProjectRequest $request, int $id, UpdateProjectUseCase $updateProject): RedirectResponse
    {
        $updateProject->execute($id, $request->toInput());

        return redirect()->route('projects.index')->with(Flash::success('案件を更新しました'));
    }

    /**
     * 売上分析。
     *
     * TODO: 集計は年月の指定だけで、取引先や状態での絞り込みはまだ無い
     *       (移行前からの TODO)。
     */
    public function analysis(Request $request, AnalyzeProjectSalesUseCase $analyze): View
    {
        $criteria = SalesAnalysisCriteria::of(
            $request->input('type'),
            $request->input('year'),
            $request->input('month'),
        );

        $analysis = $analyze->execute($criteria);

        return view('projects.analysis', [
            'projects' => ProjectView::collection($analysis->projects),
            'total_price' => $analysis->totalPrice()->amount,
            'year' => $analysis->year,
            'month' => $analysis->month,
            'search_column' => $analysis->dateField,
            'columns' => SalesAnalysisCriteria::DATE_FIELDS,
        ]);
    }
}
