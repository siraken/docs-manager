<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Academy\Entity\AcademyInquiry;
use App\Domain\Academy\Repository\AcademyInquiryRepositoryInterface;
use App\Domain\User\ValueObject\EmailAddress;
use App\Infrastructure\Persistence\Eloquent\Models\LumoUser;

final class AcademyInquiryRepository implements AcademyInquiryRepositoryInterface
{
    /** @return list<AcademyInquiry> */
    public function listAll(): array
    {
        return LumoUser::orderBy('id')->get()
            ->map(static fn (LumoUser $model): AcademyInquiry => AcademyInquiry::reconstitute(
                id: (int) $model->id,
                name: (string) $model->name,
                email: EmailAddress::fromStorage((string) $model->email),
                inquiry: (string) $model->inquiry,
            ))
            ->all();
    }

    public function save(AcademyInquiry $inquiry): AcademyInquiry
    {
        $model = new LumoUser();
        $model->fill([
            'name' => $inquiry->name(),
            'email' => (string) $inquiry->email(),
            'inquiry' => $inquiry->inquiry(),
        ]);
        $model->save();

        $inquiry->assignId((int) $model->id);

        return $inquiry;
    }
}
