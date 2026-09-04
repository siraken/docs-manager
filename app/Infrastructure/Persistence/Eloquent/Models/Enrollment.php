<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * enrollments テーブル (受講記録)。
 * 永続化の詳細で、ドメイン層はこのクラスを知らない。
 */
class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'status',
        'started_at',
        'completed_at',
        'note',
    ];
}
