<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** submissions テーブル (提出物)。ドメイン層はこのクラスを知らない。 */
class Submission extends Model
{
    use HasFactory;

    protected $fillable = ['assignment_id', 'user_id', 'status', 'submitted_at', 'body', 'feedback'];
}
