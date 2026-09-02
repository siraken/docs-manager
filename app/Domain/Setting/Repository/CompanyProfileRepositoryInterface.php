<?php

declare(strict_types=1);

namespace App\Domain\Setting\Repository;

use App\Domain\Setting\Entity\CompanyProfile;

/**
 * 自社情報の永続化。settings テーブルは 1 レコードだけを使う想定。
 */
interface CompanyProfileRepositoryInterface
{
    /** 未登録なら null */
    public function find(): ?CompanyProfile;

    public function save(CompanyProfile $profile): CompanyProfile;
}
