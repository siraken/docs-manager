<?php

declare(strict_types=1);

namespace App\Application\Setting\UseCase;

use App\Domain\Setting\Entity\CompanyProfile;
use App\Domain\Setting\Repository\CompanyProfileRepositoryInterface;

/**
 * 自社情報を取得する。未登録なら既定値を返すので、呼び出し側は null を扱わなくてよい。
 */
final readonly class GetCompanyProfileUseCase
{
    public function __construct(private CompanyProfileRepositoryInterface $profiles)
    {
    }

    public function execute(): CompanyProfile
    {
        return $this->profiles->find() ?? CompanyProfile::default();
    }
}
