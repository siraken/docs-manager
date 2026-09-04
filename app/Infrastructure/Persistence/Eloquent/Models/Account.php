<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * accounts テーブル (勘定科目マスタ)。
 * 永続化の詳細で、ドメイン層はこのクラスを知らない。
 */
class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'is_active',
        'note',
    ];
}
