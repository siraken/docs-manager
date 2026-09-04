<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * reports テーブル。永続化の詳細で、ドメイン層はこのクラスを知らない。
 */
class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'project_id',
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'work_minutes',
    ];
}
