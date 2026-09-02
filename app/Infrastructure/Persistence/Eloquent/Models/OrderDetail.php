<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * order_details テーブル。永続化の詳細で、ドメイン層はこのクラスを知らない。
 * price には税込金額が入る。
 */
class OrderDetail extends Model
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
