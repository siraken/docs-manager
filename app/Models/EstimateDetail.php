<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'slip_id',
        'item_name',
        'quantity',
        'unit',
        'cost',
        'tax_id',
        'price',
    ];
}
