<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'quantity',
        'unit',
        'cost',
        'tax_id',
        'price',
    ];
}
