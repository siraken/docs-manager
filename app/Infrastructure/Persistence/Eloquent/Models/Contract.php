<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * contracts テーブル。永続化の詳細で、ドメイン層はこのクラスを知らない。
 */
class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contract_no',
        'customer_id',
        'start_date',
        'end_date',
        'description',
    ];
}
