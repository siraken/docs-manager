<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Work extends Model
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
        'status',
    ];
}
