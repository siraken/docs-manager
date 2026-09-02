<?php

declare(strict_types=1);

namespace App\Application\Academy\UseCase;

use App\Application\Academy\Input\AcademyInquiryInput;
use App\Domain\Academy\Entity\AcademyInquiry;
use App\Domain\Academy\Repository\AcademyInquiryRepositoryInterface;
use App\Domain\User\ValueObject\EmailAddress;

/**
 * Lumo Academy の問い合わせ登録 (認証不要の公開エンドポイント)。
 *
 * 移行前は fill() だけで save() しておらず、保存されないまま値を返していた。
 *
 * TODO: 公開エンドポイントなのにレート制限も spam 対策も無い。
 *       api の throttle と同じ仕組みをこのルートにも掛けること。
 */
final readonly class RegisterAcademyInquiryUseCase
{
    public function __construct(private AcademyInquiryRepositoryInterface $inquiries)
    {
    }

    public function execute(AcademyInquiryInput $input): AcademyInquiry
    {
        $inquiry = AcademyInquiry::create(
            name: $input->name,
            email: EmailAddress::fromString($input->email),
            inquiry: $input->inquiry,
        );

        return $this->inquiries->save($inquiry);
    }
}
