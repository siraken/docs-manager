<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Customer\UseCase\ListCustomersUseCase;
use App\Application\Project\UseCase\ListProjectsUseCase;
use App\Application\Report\Input\ReportFilter;
use App\Application\Report\UseCase\CreateReportUseCase;
use App\Application\Report\UseCase\DeleteReportUseCase;
use App\Application\Report\UseCase\GetReportUseCase;
use App\Application\Report\UseCase\ListReportsUseCase;
use App\Application\Report\UseCase\UpdateReportUseCase;
use App\Application\User\UseCase\ListUsersUseCase;
use App\Domain\Customer\Entity\Customer;
use App\Domain\Project\Entity\Project;
use App\Domain\Report\Entity\Report;
use App\Domain\User\Entity\User;
use App\Http\Requests\SaveReportRequest;
use App\Http\ViewModels\CustomerView;
use App\Http\ViewModels\ProjectView;
use App\Http\ViewModels\ReportView;
use App\Http\ViewModels\UserView;
use App\Support\Flash;
use App\Support\Lookup;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * 勤務報告。in-house-timecard-app から移植した機能。
 *
 * 移植元からの主な変更:
 *  - 年月の絞り込みがルートパラメータ待ちで、GET で送られる year / month を
 *    見ていなかった。ReportFilter が受け取るようにした
 *  - 集計 (総勤務時間・総勤務日数) がページ内の行だけを対象にしていた、
 *    かつ存在しないカラムを合計していた。絞り込み結果の全件で計算する
 *  - 削除のメソッド名 (delete) がルート (destroy) と一致していなかった
 *  - $request->all() を検証なしで fill() していた
 */
final class ReportController extends Controller
{
    public function index(
        Request $request,
        ListReportsUseCase $listReports,
        ListUsersUseCase $listUsers,
        ListCustomersUseCase $listCustomers,
        ListProjectsUseCase $listProjects,
    ): InertiaResponse {
        $filter = ReportFilter::of(
            $request->input('year'),
            $request->input('month'),
            $request->input('q'),
        );

        $summary = $listReports->execute($filter);

        return Inertia::render('Reports/Index', [
            'reports' => ReportView::collection(
                $summary->reports,
                $this->userNames($listUsers->execute()),
                $this->customerNames($listCustomers->execute()),
                $this->projectNames($listProjects->execute()),
            ),
            'summary' => [
                'totalWorkMinutes' => $summary->totalWorkTime->minutes,
                'totalWorkHours' => $summary->totalWorkTime->hours(),
                'totalWorkTimeLabel' => $summary->totalWorkTime->format(),
                'workDays' => $summary->workDays,
            ],
            'filter' => [
                'year' => $filter->year,
                'month' => $filter->month,
                'keyword' => $filter->keyword,
            ],
            'years' => $this->years(),
            'urls' => [
                'self' => route('reports.index'),
                'create' => route('reports.create'),
            ],
        ]);
    }

    public function create(
        ListUsersUseCase $listUsers,
        ListCustomersUseCase $listCustomers,
        ListProjectsUseCase $listProjects,
    ): InertiaResponse {
        return Inertia::render('Reports/Form', [
            'report' => null,
            // 日付の既定値はサーバーで決める。画面が new Date() を持つと
            // サーバーの時計とずれるうえ、テストから固定できない
            'defaults' => [
                'date' => date('Y-m-d'),
                // ログイン中のユーザーを担当者の初期値にする
                'userId' => session('user_id') === null ? null : (int) session('user_id'),
            ],
            ...$this->formOptions($listUsers, $listCustomers, $listProjects),
            'urls' => [
                'submit' => route('reports.create'),
                'back' => route('reports.index'),
            ],
        ]);
    }

    public function store(SaveReportRequest $request, CreateReportUseCase $createReport): RedirectResponse
    {
        $createReport->execute($request->toInput());

        return redirect()->route('reports.index')->with(Flash::success('勤務報告を登録しました'));
    }

    /**
     * 詳細画面。移植元は reports/show.blade.php が空で、開いても
     * 何も表示されなかった。
     */
    public function show(
        int $id,
        GetReportUseCase $getReport,
        ListUsersUseCase $listUsers,
        ListCustomersUseCase $listCustomers,
        ListProjectsUseCase $listProjects,
    ): InertiaResponse {
        $report = $getReport->execute($id);

        return Inertia::render('Reports/Show', [
            'report' => $this->viewFor($report, $listUsers, $listCustomers, $listProjects),
            'urls' => [
                'back' => route('reports.index'),
            ],
        ]);
    }

    public function edit(
        int $id,
        GetReportUseCase $getReport,
        ListUsersUseCase $listUsers,
        ListCustomersUseCase $listCustomers,
        ListProjectsUseCase $listProjects,
    ): InertiaResponse {
        $report = $getReport->execute($id);

        return Inertia::render('Reports/Form', [
            'report' => $this->viewFor($report, $listUsers, $listCustomers, $listProjects),
            'defaults' => null,
            ...$this->formOptions($listUsers, $listCustomers, $listProjects),
            'urls' => [
                'submit' => route('reports.edit', ['id' => $id]),
                'back' => route('reports.index'),
            ],
        ]);
    }

    public function update(
        SaveReportRequest $request,
        int $id,
        UpdateReportUseCase $updateReport,
    ): RedirectResponse {
        $updateReport->execute($id, $request->toInput());

        return redirect()->route('reports.index')->with(Flash::success('勤務報告を更新しました'));
    }

    public function destroy(int $id, DeleteReportUseCase $deleteReport): RedirectResponse
    {
        $deleteReport->execute($id);

        return redirect()->route('reports.index')->with(Flash::success('勤務報告を削除しました'));
    }

    /**
     * フォームのセレクトの選択肢。新規・編集の双方で同じものを渡す。
     *
     * @return array<string, mixed>
     */
    private function formOptions(
        ListUsersUseCase $listUsers,
        ListCustomersUseCase $listCustomers,
        ListProjectsUseCase $listProjects,
    ): array {
        return [
            'users' => UserView::options($listUsers->execute()),
            'customers' => CustomerView::options($listCustomers->execute()),
            'projects' => ProjectView::options($listProjects->execute()),
        ];
    }

    /** 1 件分の ViewModel。名前の解決に必要なマスタをまとめて引く */
    private function viewFor(
        Report $report,
        ListUsersUseCase $listUsers,
        ListCustomersUseCase $listCustomers,
        ListProjectsUseCase $listProjects,
    ): ReportView {
        return ReportView::fromEntity(
            $report,
            $this->userNames($listUsers->execute())[$report->userId()] ?? '',
            $this->customerNames($listCustomers->execute())[$report->customerId()] ?? '',
            $this->projectNames($listProjects->execute())[$report->projectId()] ?? '',
        );
    }

    /**
     * 年の選択肢。移植元は 2020〜2024 をビューに直書きしていたため、
     * 2025 年になった時点で当年を選べなくなっていた。
     *
     * @return list<int>
     */
    private function years(): array
    {
        return range(2020, (int) date('Y') + 1);
    }

    /**
     * @param list<User> $users
     * @return array<int, string>
     */
    private function userNames(array $users): array
    {
        return Lookup::byId($users, static fn (User $u): string => $u->name());
    }

    /**
     * @param list<Customer> $customers
     * @return array<int, string>
     */
    private function customerNames(array $customers): array
    {
        return Lookup::byId($customers, static fn (Customer $c): string => $c->name());
    }

    /**
     * @param list<Project> $projects
     * @return array<int, string>
     */
    private function projectNames(array $projects): array
    {
        return Lookup::byId($projects, static fn (Project $p): string => $p->name());
    }
}
