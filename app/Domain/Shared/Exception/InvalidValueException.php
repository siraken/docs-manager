<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exception;

/**
 * 値オブジェクトの不変条件を満たさない値を渡されたことを表す。
 *
 * 入力の形式チェックは FormRequest (app/Http/Requests) が先に行うが、
 * 値オブジェクト側でも不変条件を守る (ユースケースを HTTP 以外から
 * 呼んだときに壊れた値が入らないようにするため)。
 */
final class InvalidValueException extends DomainException
{
}
