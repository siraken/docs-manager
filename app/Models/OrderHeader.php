<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHeader extends Model
{
    use HasFactory;

    protected $fillable = [
        'destination',
        'responsible',
        'honor_title',
        'issued_date',
        'exp_date',
        'order_no',
        'title',
        'subtotal_price',
        'tax_price',
        'total_price',
        'remarks',
        'is_issued',
        'is_ordered',
        'is_deleted',
        'is_converted',
        // 'reg_uid',
    ];
}
