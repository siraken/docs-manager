<?php

declare(strict_types=1);

namespace App\Application\Travel\UseCase;

use App\Application\Shared\Output\RenderedDocument;
use App\Application\Travel\Port\TravelExpensePdfRendererInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\Travel\Repository\TravelExpenseRepositoryInterface;

final readonly class RenderTravelExpensePdfUseCase
{
    public function __construct(
        private TravelExpenseRepositoryInterface $expenses,
        private TravelExpensePdfRendererInterface $renderer,
    ) {
    }

    public function execute(int $id): RenderedDocument
    {
        $expense = $this->expenses->findById($id)
            ?? throw EntityNotFoundException::of('出張旅費精算', $id);

        return new RenderedDocument(
            fileName: sprintf('biztrip_%d_%s.pdf', $expense->id(), $expense->applyDate()->format('Ymd')),
            contentType: 'application/pdf',
            contents: $this->renderer->render($expense),
        );
    }
}
