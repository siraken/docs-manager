<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Customer\UseCase\ListCustomersUseCase;
use App\Application\Project\Input\SalesAnalysisCriteria;
use App\Application\Project\UseCase\AnalyzeProjectSalesUseCase;
use App\Application\Project\UseCase\CreateProjectUseCase;
use App\Application\Project\UseCase\GetProjectUseCase;
use App\Application\Project\UseCase\ListProjectsUseCase;
use App\Application\Project\UseCase\UpdateProjectUseCase;
use App\Domain\Customer\Entity\Customer;
use App\Domain\Project\ValueObject\ProjectStatus;
use App\Http\Requests\SaveProjectRequest;
use App\Support\Flash;
use App\Http\ViewModels\CustomerView;
use App\Http\ViewModels\ProjectView;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

final class ProjectController extends Controller
{
    public function index(ListProjectsUseCase $listProjects, ListCustomersUseCase $listCustomers): InertiaResponse
    {
        return Inertia::render('Projects/Index', [
            'projects' => ProjectView::collection(
                $listProjects->execute(),
                $this->customerNames($listCustomers->execute()),
                $this->jiraBrowseUrl(),
            ),
            'urls' => [
                'create' => route('projects.create'),
                'analysis' => route('projects.analysis'),
            ],
        ]);
    }

    public function create(ListCustomersUseCase $listCustomers): InertiaResponse
    {
        return Inertia::render('Projects/Form', [
            'project' => null,
            'statuses' => $this->statusOptions(),
            'customers' => CustomerView::options($listCustomers->execute()),
            'urls' => [
                'submit' => route('projects.create'),
                'back' => route('projects.index'),
            ],
        ]);
    }

    public function store(SaveProjectRequest $request, CreateProjectUseCase $createProject): RedirectResponse
    {
        $createProject->execute($request->toInput());

        return redirect()->route('projects.index')->with(Flash::success('案件を登録しました'));
    }

    public function edit(int $id, GetProjectUseCase $getProject, ListCustomersUseCase $listCustomers): InertiaResponse
    {
        $customers = $listCustomers->execute();
        $project = $getProject->execute($id);

        return Inertia::render('Projects/Form', [
            'project' => ProjectView::fromEntity(
                $project,
                $this->customerNames($customers)[$project->clientId()] ?? '',
                $this->jiraBrowseUrl(),
            ),
            'statuses' => $this->statusOptions(),
            'customers' => CustomerView::options($customers),
            'urls' => [
                'submit' => route('projects.edit', ['id' => $id]),
                'back' => route('projects.index'),
            ],
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
    public function analysis(
        Request $request,
        AnalyzeProjectSalesUseCase $analyze,
        ListCustomersUseCase $listCustomers,
    ): InertiaResponse {
        $criteria = SalesAnalysisCriteria::of(
            $request->input('type'),
            $request->input('year'),
            $request->input('month'),
        );

        $analysis = $analyze->execute($criteria);

        return Inertia::render('Projects/Analysis', [
            'projects' => ProjectView::collection(
                $analysis->projects,
                $this->customerNames($listCustomers->execute()),
                $this->jiraBrowseUrl(),
            ),
            'totalPrice' => $analysis->totalPrice()->amount,
            'totalPriceLabel' => $analysis->totalPrice()->format(),
            'criteria' => [
                'dateField' => $analysis->dateField,
                'year' => $analysis->year,
                'month' => $analysis->month,
            ],
            'columns' => $this->columnOptions(),
            'years' => range(2020, (int) date('Y') + 1),
            'urls' => [
                'self' => route('projects.analysis'),
                'back' => route('projects.index'),
            ],
        ]);
    }

    /**
     * Jira の /browse/ の URL。env() は直接読まない (config:cache 済みの環境で
     * null になるため)。
     */
    private function jiraBrowseUrl(): string
    {
        return (string) config('services.jira.browse_url', '');
    }

    /**
     * 顧客 ID => 顧客名。一覧で案件ごとに顧客を引くと N+1 になるため、
     * まとめて引いて対応表にする (OrderController と同じ形)。
     *
     * 移行前の一覧は取引先の列に顧客 ID をそのまま出しており、フォームも
     * 顧客 ID を手で打ち込ませていた。
     *
     * @param list<Customer> $customers
     * @return array<int, string>
     */
    private function customerNames(array $customers): array
    {
        $names = [];

        foreach ($customers as $customer) {
            $names[(int) $customer->id()] = $customer->name();
        }

        return $names;
    }

    /**
     * 状態の選択肢。ドメインの ProjectStatus が唯一の定義。
     *
     * 移行前はフォームのビューに 8 種類、一覧のコントローラに 3 種類という
     * 食い違った定義が別々に書かれていた。
     *
     * @return list<array{value: int, label: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            static fn (int $value, string $label): array => ['value' => $value, 'label' => $label],
            array_keys(ProjectStatus::options()),
            array_values(ProjectStatus::options()),
        );
    }

    /**
     * 集計対象にできる日付カラム。ここに無い値はユースケース側で弾かれる。
     *
     * @return list<array{value: string, label: string}>
     */
    private function columnOptions(): array
    {
        return array_map(
            static fn (string $value, string $label): array => ['value' => $value, 'label' => $label],
            array_keys(SalesAnalysisCriteria::DATE_FIELDS),
            array_values(SalesAnalysisCriteria::DATE_FIELDS),
        );
    }
}
