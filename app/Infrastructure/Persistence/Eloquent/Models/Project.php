<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * projects テーブル。永続化の詳細で、ドメイン層はこのクラスを知らない。
 */
class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'client_id',
        'related_task_id',
        'start_date',
        'end_date',
        'payment_date',
        // price は移行前の $fillable から漏れていた (コントローラが個別代入して
        // いたため表面化していなかった)
        'price',
        'status',
    ];
}
