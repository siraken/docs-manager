<?php

declare(strict_types=1);

namespace App\Application\Travel\UseCase;

use App\Application\Shared\Output\RenderedDocument;
use App\Application\Travel\Port\TravelPdfRendererInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\Travel\Repository\TravelRepositoryInterface;

final readonly class RenderTravelPdfUseCase
{
    public function __construct(
        private TravelRepositoryInterface $travels,
        private TravelPdfRendererInterface $renderer,
    ) {
    }

    public function execute(int $id): RenderedDocument
    {
        $travel = $this->travels->findById($id)
            ?? throw EntityNotFoundException::of('出張申請', $id);

        return new RenderedDocument(
            // 移行前は常に「当日の日付.pdf」だったため、複数の申請を続けて
            // 落とすとファイル名がぶつかっていた。申請 ID を付けて区別する。
            fileName: sprintf('trip_%d_%s.pdf', $travel->id(), $travel->applyDate()->format('Ymd')),
            contentType: 'application/pdf',
            contents: $this->renderer->render($travel),
        );
    }
}
