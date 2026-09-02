<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * lumo_users テーブル (Lumo Academy の問い合わせ)。
 * 永続化の詳細で、ドメイン層はこのクラスを知らない。
 */
class LumoUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'inquiry',
    ];
}
