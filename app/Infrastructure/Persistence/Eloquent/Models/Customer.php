<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * customers テーブル。永続化の詳細で、ドメイン層はこのクラスを知らない。
 * ドメインエンティティとの相互変換は CustomerMapper が行う。
 */
class Customer extends Model
{
    use HasFactory;

    /**
     * 移行前は $fillable が空で、コントローラが 1 カラムずつ代入していた。
     * リポジトリから fill() で書けるように定義しておく。
     */
    protected $fillable = [
        'name',
        'is_company',
        'email',
        'phone',
        'post_code',
        'address',
        'city',
        'state',
        'country',
        'note',
    ];
}
