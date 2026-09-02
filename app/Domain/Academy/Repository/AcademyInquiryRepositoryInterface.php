<?php

declare(strict_types=1);

namespace App\Domain\Academy\Repository;

use App\Domain\Academy\Entity\AcademyInquiry;

interface AcademyInquiryRepositoryInterface
{
    /** @return list<AcademyInquiry> */
    public function listAll(): array;

    public function save(AcademyInquiry $inquiry): AcademyInquiry;
}
