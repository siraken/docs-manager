<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Shared\Output\RenderedDocument;
use App\Application\Travel\UseCase\CreateTravelExpenseUseCase;
use App\Application\Travel\UseCase\GetTravelExpenseUseCase;
use App\Application\Travel\UseCase\ImportTravelExpenseCsvUseCase;
use App\Application\Travel\UseCase\ListTravelExpensesUseCase;
use App\Application\Travel\UseCase\RenderTravelExpensePdfUseCase;
use App\Application\Travel\UseCase\UpdateTravelExpenseUseCase;
use App\Domain\Shared\Exception\DomainException;
use App\Http\Requests\ImportCsvRequest;
use App\Http\Requests\SaveTravelExpenseRequest;
use App\Support\Flash;
use App\Http\ViewModels\TravelExpenseView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

final class TravelExpenseController extends Controller
{
    public function index(ListTravelExpensesUseCase $listExpenses): InertiaResponse
    {
        return Inertia::render('Expenses/Index', [
            'expenses' => TravelExpenseView::collection($listExpenses->execute()),
            'urls' => [
                'create' => route('expenses.create'),
                'import' => route('expenses.import'),
            ],
        ]);
    }

    /**
     * 精算フォーム。
     *
     * 新規でも empty() を渡す。管理 ID や日付の既定値をここが持っているため
     * (null にすると画面側で同じ既定値をもう一度書くことになる)。
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('Expenses/Form', [
            'expense' => TravelExpenseView::empty(),
            'isNew' => true,
            'urls' => [
                'submit' => route('expenses.create'),
                'back' => route('expenses.index'),
            ],
        ]);
    }

    public function store(SaveTravelExpenseRequest $request, CreateTravelExpenseUseCase $createExpense): RedirectResponse
    {
        $createExpense->execute($request->toInput());

        return redirect()->route('expenses.index')->with(Flash::success('旅費精算を登録しました'));
    }

    public function edit(int $id, GetTravelExpenseUseCase $getExpense): InertiaResponse
    {
        return Inertia::render('Expenses/Form', [
            'expense' => TravelExpenseView::fromEntity($getExpense->execute($id)),
            'isNew' => false,
            'urls' => [
                'submit' => route('expenses.edit', ['id' => $id]),
                'back' => route('expenses.index'),
            ],
        ]);
    }

    public function update(SaveTravelExpenseRequest $request, int $id, UpdateTravelExpenseUseCase $updateExpense): RedirectResponse
    {
        $updateExpense->execute($id, $request->toInput());

        return redirect()->route('expenses.index')->with(Flash::success('旅費精算を更新しました'));
    }

    public function show(int $id, GetTravelExpenseUseCase $getExpense): InertiaResponse
    {
        return Inertia::render('Expenses/Show', [
            'expense' => TravelExpenseView::fromEntity($getExpense->execute($id)),
            'urls' => ['back' => route('expenses.index')],
        ]);
    }

    public function pdf(int $id, RenderTravelExpensePdfUseCase $renderPdf): Response
    {
        return $this->inline($renderPdf->execute($id));
    }

    /**
     * CSV 取り込み。
     *
     * 移行前は取り込み後のリダイレクト先が /trips (出張申請の一覧) になっていた。
     * 精算の一覧に戻す。
     */
    public function csvImport(ImportCsvRequest $request, ImportTravelExpenseCsvUseCase $import): RedirectResponse
    {
        try {
            $count = $import->execute($request->csvPath(), $request->hasHeaderRow());
        } catch (DomainException $e) {
            return redirect()->route('expenses.index')
                ->with(Flash::error('CSV の取り込みに失敗しました: ' . $e->getMessage()));
        }

        return redirect()->route('expenses.index')
            ->with(Flash::success(sprintf('%d 件取り込みました', $count)));
    }

    private function inline(RenderedDocument $document): Response
    {
        return response($document->contents, 200, [
            'Content-Type' => $document->contentType,
            'Content-Disposition' => sprintf('inline; filename="%s"', $document->fileName),
        ]);
    }
}
