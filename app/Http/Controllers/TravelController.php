<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Shared\Output\RenderedDocument;
use App\Application\Travel\UseCase\CreateTravelUseCase;
use App\Application\Travel\UseCase\GetTravelUseCase;
use App\Application\Travel\UseCase\ImportTravelCsvUseCase;
use App\Application\Travel\UseCase\ListTravelsUseCase;
use App\Application\Travel\UseCase\RenderTravelPdfUseCase;
use App\Domain\Shared\Exception\DomainException;
use App\Http\Requests\ImportCsvRequest;
use App\Http\Requests\SaveTravelRequest;
use App\Support\Flash;
use App\Http\ViewModels\TravelView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

final class TravelController extends Controller
{
    public function index(ListTravelsUseCase $listTravels): View
    {
        return view('calculate.trips.index', [
            'trips' => TravelView::collection($listTravels->execute()),
        ]);
    }

    public function create(): View
    {
        return view('calculate.trips.form');
    }

    public function store(SaveTravelRequest $request, CreateTravelUseCase $createTravel): RedirectResponse
    {
        $createTravel->execute($request->toInput());

        return redirect()->route('trips.index')->with(Flash::success('出張申請を登録しました'));
    }

    public function show(int $id, GetTravelUseCase $getTravel): View
    {
        return view('calculate.trips.view', [
            'trip' => TravelView::fromEntity($getTravel->execute($id)),
        ]);
    }

    public function pdf(int $id, RenderTravelPdfUseCase $renderPdf): Response
    {
        return $this->inline($renderPdf->execute($id));
    }

    /**
     * CSV 取り込み。
     *
     * 移行前は失敗しても一律「Failed」としか出せず、何行取り込めたかも
     * 分からなかった。件数を伝え、壊れた行があれば全体を取り消す。
     */
    public function csvImport(ImportCsvRequest $request, ImportTravelCsvUseCase $import): RedirectResponse
    {
        try {
            $count = $import->execute($request->csvPath(), $request->hasHeaderRow());
        } catch (DomainException $e) {
            return redirect()->route('trips.index')
                ->with(Flash::error('CSV の取り込みに失敗しました: ' . $e->getMessage()));
        }

        return redirect()->route('trips.index')
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
