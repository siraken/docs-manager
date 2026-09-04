<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mapper;

use App\Application\Shared\DateParser;
use App\Domain\Accounting\Entity\JournalEntry as JournalEntryEntity;
use App\Domain\Shared\ValueObject\Money;
use App\Infrastructure\Persistence\Eloquent\Models\JournalEntry as JournalEntryModel;

final class JournalEntryMapper
{
    public static function toDomain(JournalEntryModel $model): JournalEntryEntity
    {
        return JournalEntryEntity::reconstitute(
            id: (int) $model->id,
            date: DateParser::parse($model->date, '日付'),
            // sqlite は integer カラムを文字列で返す
            debitAccountId: (int) $model->debit_account_id,
            creditAccountId: (int) $model->credit_account_id,
            amount: Money::fromNumeric($model->amount),
            description: (string) $model->description,
            note: $model->note,
        );
    }

    /** @return array<string, mixed> */
    public static function toAttributes(JournalEntryEntity $entry): array
    {
        return [
            'date' => $entry->date()->format('Y-m-d'),
            'debit_account_id' => $entry->debitAccountId(),
            'credit_account_id' => $entry->creditAccountId(),
            'amount' => $entry->amount()->amount,
            'description' => $entry->description(),
            'note' => $entry->note(),
        ];
    }
}
