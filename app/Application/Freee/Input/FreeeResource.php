<?php

declare(strict_types=1);

namespace App\Application\Freee\Input;

/**
 * 取得できる freee のリソース。
 *
 * 移行前はリソースごとに同じ形の cURL 呼び出しが 5 つ並んでいた。
 * URL の差しかないので、その差だけをここに持つ。
 */
enum FreeeResource: string
{
    case Companies = 'companies';
    case Walletables = 'walletables';
    case Partners = 'partners';
    case Quotations = 'quotations';
    case Invoices = 'invoices';

    /** 会社 ID をクエリに付ける必要があるか (companies だけは不要) */
    public function requiresCompanyId(): bool
    {
        return $this !== self::Companies;
    }
}
