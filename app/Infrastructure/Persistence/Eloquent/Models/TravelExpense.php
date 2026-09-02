<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * travel_expenses テーブル。永続化の詳細で、ドメイン層はこのクラスを知らない。
 */
class TravelExpense extends Model
{
    use HasFactory;

    /**
     * 移行前は $fillable が空のまま fill($request->all()) を呼んでおり、
     * 保存されるカラムが 1 つも無かった (MassAssignmentException にはならず、
     * 単に無視される)。実際に使うカラムを列挙しておく。
     */
    protected $fillable = [
        'rel_id',
        'dir',
        'purpose',
        'apply_person',
        'apply_date',
        'date_from',
        'date_to',
        'pay_date',
        'trans_fee',
        'acm_fee',
        'gas_fee',
        'dinner_fee',
        'lunch_fee',
        'daily_pay',
        'total_fee',
    ];
}
