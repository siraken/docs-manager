<?php

declare(strict_types=1);

namespace App\Application\Freee\Exception;

/**
 * freee API の呼び出しに失敗した。
 * 通信エラーと、2xx 以外のレスポンスの両方をこれで表す。
 */
final class FreeeApiException extends \RuntimeException
{
}
