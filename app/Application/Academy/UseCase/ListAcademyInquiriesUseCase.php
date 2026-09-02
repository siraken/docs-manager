<?php

declare(strict_types=1);

namespace App\Application\Academy\UseCase;

use App\Domain\Academy\Entity\AcademyInquiry;
use App\Domain\Academy\Repository\AcademyInquiryRepositoryInterface;

/**
 * 問い合わせ一覧。
 *
 * 移行前の AcademyController::index() は中身が空で、何も返していなかった
 * (ルートは登録済み)。登録された問い合わせを確認する画面が要るはずなので、
 * 一覧を返す形で埋めている。
 */
final readonly class ListAcademyInquiriesUseCase
{
    public function __construct(private AcademyInquiryRepositoryInterface $inquiries)
    {
    }

    /** @return list<AcademyInquiry> */
    public function execute(): array
    {
        return $this->inquiries->listAll();
    }
}
