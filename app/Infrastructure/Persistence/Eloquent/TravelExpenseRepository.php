<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\Shared\DateParser;
use App\Domain\Shared\ValueObject\Money;
use App\Domain\Travel\Entity\TravelExpense;
use App\Domain\Travel\Repository\TravelExpenseRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\TravelExpense as TravelExpenseModel;
use Illuminate\Support\Facades\DB;

final class TravelExpenseRepository implements TravelExpenseRepositoryInterface
{
    /** @return list<TravelExpense> */
    public function listAll(): array
    {
        return TravelExpenseModel::orderBy('id')->get()
            ->map(self::toDomain(...))
            ->all();
    }

    public function findById(int $id): ?TravelExpense
    {
        $model = TravelExpenseModel::find($id);

        return $model === null ? null : self::toDomain($model);
    }

    public function save(TravelExpense $expense): TravelExpense
    {
        $model = $expense->id() === null
            ? new TravelExpenseModel()
            : TravelExpenseModel::find($expense->id()) ?? new TravelExpenseModel();

        $model->fill(self::toAttributes($expense));
        $model->save();

        $expense->assignId((int) $model->id);

        return $expense;
    }

    /** @param list<TravelExpense> $expenses */
    public function saveAll(array $expenses): int
    {
        return DB::transaction(function () use ($expenses): int {
            foreach ($expenses as $expense) {
                $this->save($expense);
            }

            return count($expenses);
        });
    }

    private static function toDomain(TravelExpenseModel $model): TravelExpense
    {
        return TravelExpense::reconstitute(
            id: (int) $model->id,
            relId: (string) $model->rel_id,
            destination: (string) $model->dir,
            purpose: (string) $model->purpose,
            applyDate: DateParser::parse($model->apply_date, '申請日'),
            dateFrom: DateParser::parse($model->date_from, '出発日'),
            dateTo: DateParser::parse($model->date_to, '帰着日'),
            payDate: DateParser::parse($model->pay_date, '精算日'),
            applyPerson: (string) $model->apply_person,
            transportationFee: Money::fromNumeric($model->trans_fee ?? 0),
            accommodationFee: Money::fromNumeric($model->acm_fee ?? 0),
            gasFee: Money::fromNumeric($model->gas_fee ?? 0),
            dinnerFee: Money::fromNumeric($model->dinner_fee ?? 0),
            lunchFee: Money::fromNumeric($model->lunch_fee ?? 0),
            dailyAllowance: Money::fromNumeric($model->daily_pay ?? 0),
        );
    }

    /** @return array<string, mixed> */
    private static function toAttributes(TravelExpense $expense): array
    {
        return [
            'rel_id' => $expense->relId(),
            'dir' => $expense->destination(),
            'purpose' => $expense->purpose(),
            'apply_person' => $expense->applyPerson(),
            'apply_date' => $expense->applyDate()->format('Y-m-d'),
            'date_from' => $expense->dateFrom()->format('Y-m-d'),
            'date_to' => $expense->dateTo()->format('Y-m-d'),
            'pay_date' => $expense->payDate()->format('Y-m-d'),
            'trans_fee' => (string) $expense->transportationFee()->amount,
            'acm_fee' => (string) $expense->accommodationFee()->amount,
            'gas_fee' => (string) $expense->gasFee()->amount,
            'dinner_fee' => (string) $expense->dinnerFee()->amount,
            'lunch_fee' => (string) $expense->lunchFee()->amount,
            'daily_pay' => (string) $expense->dailyAllowance()->amount,
            // 合計は内訳から導出した値を書く (CSV やフォームの値は使わない)
            'total_fee' => (string) $expense->totalFee()->amount,
        ];
    }
}
