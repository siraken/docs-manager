<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** assignments テーブル (課題)。ドメイン層はこのクラスを知らない。 */
class Assignment extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'title', 'description', 'due_on'];
}
