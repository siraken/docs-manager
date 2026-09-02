<?php

declare(strict_types=1);

namespace App\Application\Order\UseCase;

use App\Application\Shared\Output\RenderedDocument;
use App\Application\Order\Port\OrderPdfRendererInterface;
use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Setting\Repository\CompanyProfileRepositoryInterface;
use App\Domain\Setting\Entity\CompanyProfile;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * 発注書 PDF を組み立てて返す。
 *
 * 移行前は宛先の顧客を無条件に参照していたため、顧客が削除されていると
 * 500 になっていた。宛名は空で描画して PDF 自体は出す。
 */
final readonly class RenderOrderPdfUseCase
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private CustomerRepositoryInterface $customers,
        private CompanyProfileRepositoryInterface $companyProfiles,
        private OrderPdfRendererInterface $renderer,
    ) {
    }

    public function execute(int $id): RenderedDocument
    {
        $order = $this->orders->findById($id)
            ?? throw EntityNotFoundException::of('発注書', $id);

        $customerName = $this->customers->findById($order->customerId())?->name() ?? '';

        return new RenderedDocument(
            fileName: $order->orderNo()->toFileName('pdf'),
            contentType: 'application/pdf',
            contents: $this->renderer->render(
                $order,
                $customerName,
                // 差出人欄は移行前 PDF に直書きされていた。設定が未登録なら
                // その直書き値と同じ既定値が返る。
                $this->companyProfiles->find() ?? CompanyProfile::default(),
            ),
        );
    }
}
