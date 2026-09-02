<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exception;

/**
 * ドメイン層で投げる例外の基底。
 *
 * ドメイン層はフレームワークに依存しないため、Laravel の例外は使わない。
 * HTTP ステータスへの変換は Presentation 層 (bootstrap/app.php の
 * withExceptions) が行う。
 */
abstract class DomainException extends \DomainException
{
}
