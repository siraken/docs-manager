<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * settings テーブル (自社情報)。1 レコードだけを使う。
 * 永続化の詳細で、ドメイン層はこのクラスを知らない。
 */
class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'zipcode',
        'address',
        'rep',
        'tel_no',
        'logo_url',
        'com_stamp_url',
        'rep_stamp_url',
        'apply_stamp_url',
    ];
}
