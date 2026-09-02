<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * travels テーブル。永続化の詳細で、ドメイン層はこのクラスを知らない。
 */
class Travel extends Model
{
    use HasFactory;

    protected $table = 'travels';
    protected $fillable = [
        'rel_id',
        'dir',
        'purpose',
        'price',
        'date_from',
        'date_to',
        'apply_date',
        'apply_person',
    ];
}
