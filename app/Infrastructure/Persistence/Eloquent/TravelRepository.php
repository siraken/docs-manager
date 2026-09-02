<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\Shared\DateParser;
use App\Domain\Shared\ValueObject\Money;
use App\Domain\Travel\Entity\Travel;
use App\Domain\Travel\Repository\TravelRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Travel as TravelModel;
use Illuminate\Support\Facades\DB;

final class TravelRepository implements TravelRepositoryInterface
{
    /** @return list<Travel> */
    public function listAll(): array
    {
        return TravelModel::orderBy('id')->get()
            ->map(self::toDomain(...))
            ->all();
    }

    public function findById(int $id): ?Travel
    {
        $model = TravelModel::find($id);

        return $model === null ? null : self::toDomain($model);
    }

    public function save(Travel $travel): Travel
    {
        $model = $travel->id() === null
            ? new TravelModel()
            : TravelModel::find($travel->id()) ?? new TravelModel();

        $model->fill(self::toAttributes($travel));
        $model->save();

        $travel->assignId((int) $model->id);

        return $travel;
    }

    /** @param list<Travel> $travels */
    public function saveAll(array $travels): int
    {
        // 1 行でも壊れていれば全体を取り消す。移行前は 1 件ずつ保存していたため
        // 途中で失敗すると半端に入った状態が残った。
        return DB::transaction(function () use ($travels): int {
            foreach ($travels as $travel) {
                $this->save($travel);
            }

            return count($travels);
        });
    }

    private static function toDomain(TravelModel $model): Travel
    {
        return Travel::reconstitute(
            id: (int) $model->id,
            relId: (string) $model->rel_id,
            destination: (string) $model->dir,
            purpose: (string) $model->purpose,
            price: Money::fromNumeric($model->price ?? 0),
            dateFrom: DateParser::parse($model->date_from, '出発日'),
            dateTo: DateParser::parse($model->date_to, '帰着日'),
            applyDate: DateParser::parse($model->apply_date, '申請日'),
            applyPerson: (string) $model->apply_person,
        );
    }

    /** @return array<string, mixed> */
    private static function toAttributes(Travel $travel): array
    {
        return [
            'rel_id' => $travel->relId(),
            'dir' => $travel->destination(),
            'purpose' => $travel->purpose(),
            'price' => $travel->price()->amount,
            'date_from' => $travel->dateFrom()->format('Y-m-d'),
            'date_to' => $travel->dateTo()->format('Y-m-d'),
            'apply_date' => $travel->applyDate()->format('Y-m-d'),
            'apply_person' => $travel->applyPerson(),
        ];
    }
}
