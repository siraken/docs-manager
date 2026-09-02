<?php

declare(strict_types=1);

namespace App\Application\Setting\UseCase;

use App\Application\Setting\Input\CompanyProfileInput;
use App\Domain\Setting\Entity\CompanyProfile;
use App\Domain\Setting\Repository\CompanyProfileRepositoryInterface;

/**
 * 自社情報を保存する。未登録なら新規に作る。
 */
final readonly class UpdateCompanyProfileUseCase
{
    public function __construct(private CompanyProfileRepositoryInterface $profiles)
    {
    }

    public function execute(CompanyProfileInput $input): CompanyProfile
    {
        $profile = $this->profiles->find();

        if ($profile === null) {
            $profile = CompanyProfile::create(
                name: $input->name,
                zipcode: $input->zipcode,
                address: $input->address,
                representative: $input->representative,
                telNo: $input->telNo,
                logoUrl: $input->logoUrl,
                companyStampUrl: $input->companyStampUrl,
                representativeStampUrl: $input->representativeStampUrl,
                applyStampUrl: $input->applyStampUrl,
            );
        } else {
            $profile->update(
                name: $input->name,
                zipcode: $input->zipcode,
                address: $input->address,
                representative: $input->representative,
                telNo: $input->telNo,
                logoUrl: $input->logoUrl,
                companyStampUrl: $input->companyStampUrl,
                representativeStampUrl: $input->representativeStampUrl,
                applyStampUrl: $input->applyStampUrl,
            );
        }

        return $this->profiles->save($profile);
    }
}
