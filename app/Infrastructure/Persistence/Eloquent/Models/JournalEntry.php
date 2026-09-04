<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * journal_entries テーブル (仕訳帳)。
 * 永続化の詳細で、ドメイン層はこのクラスを知らない。
 */
class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'debit_account_id',
        'credit_account_id',
        'amount',
        'description',
        'note',
    ];
}
