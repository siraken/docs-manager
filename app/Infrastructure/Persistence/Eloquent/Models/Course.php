<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * courses テーブル (講座)。
 * 永続化の詳細で、ドメイン層はこのクラスを知らない。
 */
class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'exp',
        'is_published',
    ];
}
