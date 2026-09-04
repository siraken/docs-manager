<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Contract\Entity\Contract;
use App\Domain\Contract\Repository\ContractRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Mapper\ContractMapper;
use App\Infrastructure\Persistence\Eloquent\Models\Contract as ContractModel;

final class ContractRepository implements ContractRepositoryInterface
{
    /** @return list<Contract> */
    public function listAll(): array
    {
        // 新しい契約から並べる。開始日が未設定のものは末尾へ回す
        return ContractModel::orderByRaw('start_date IS NULL')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get()
            ->map(ContractMapper::toDomain(...))
            ->all();
    }

    public function findById(int $id): ?Contract
    {
        $model = ContractModel::find($id);

        return $model === null ? null : ContractMapper::toDomain($model);
    }

    public function save(Contract $contract): Contract
    {
        $model = $contract->id() === null
            ? new ContractModel()
            : ContractModel::find($contract->id()) ?? new ContractModel();

        $model->fill(ContractMapper::toAttributes($contract));
        $model->save();

        $contract->assignId((int) $model->id);

        return $contract;
    }

    public function delete(int $id): void
    {
        ContractModel::destroy($id);
    }
}
