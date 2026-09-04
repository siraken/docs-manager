<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mapper;

use App\Application\Shared\DateParser;
use App\Domain\Contract\Entity\Contract as ContractEntity;
use App\Domain\Contract\ValueObject\ContractTerm;
use App\Infrastructure\Persistence\Eloquent\Models\Contract as ContractModel;

final class ContractMapper
{
    public static function toDomain(ContractModel $model): ContractEntity
    {
        return ContractEntity::reconstitute(
            id: (int) $model->id,
            name: (string) $model->name,
            contractNo: $model->contract_no,
            // sqlite は integer カラムを文字列で返すため int に寄せる
            customerId: $model->customer_id === null ? null : (int) $model->customer_id,
            term: ContractTerm::of(
                DateParser::parseNullable($model->start_date, '契約開始日'),
                DateParser::parseNullable($model->end_date, '契約終了日'),
            ),
            description: $model->description,
        );
    }

    /** @return array<string, mixed> */
    public static function toAttributes(ContractEntity $contract): array
    {
        return [
            'name' => $contract->name(),
            'contract_no' => $contract->contractNo(),
            'customer_id' => $contract->customerId(),
            'start_date' => $contract->term()->startDate?->format('Y-m-d'),
            'end_date' => $contract->term()->endDate?->format('Y-m-d'),
            'description' => $contract->description(),
        ];
    }
}
