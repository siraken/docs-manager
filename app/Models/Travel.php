<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
