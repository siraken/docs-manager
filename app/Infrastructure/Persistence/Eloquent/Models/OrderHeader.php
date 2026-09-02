<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * order_headers テーブル。永続化の詳細で、ドメイン層はこのクラスを知らない。
 * 明細は order_details.slip_id で紐づく (order_header_id ではない)。
 */
class OrderHeader extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
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
        // 社内メモ。一覧のドロップダウンが表示しているが、移行前は保存経路が無かった
        'note',
    ];
}
