<?php

declare(strict_types=1);

namespace App\Application\Order\Port;

use App\Domain\Order\Entity\Order;
use App\Domain\Setting\Entity\CompanyProfile;

/**
 * 発注書 PDF の描画。実装 (TCPDF/FPDI) は Infrastructure 層に置く。
 *
 * 戻り値はバイト列で、HTTP レスポンスへの載せ方は Presentation 層が決める。
 * 移行前は TCPDF の Output() が直接ブラウザに書き出していたため、
 * テストから内容を検証できず、出力後に他の処理を挟めなかった。
 */
interface OrderPdfRendererInterface
{
    public function render(Order $order, string $customerName, CompanyProfile $company): string;
}
