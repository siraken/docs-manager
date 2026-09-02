<?php

declare(strict_types=1);

namespace App\Infrastructure\Pdf;

use App\Application\Travel\Port\TravelExpensePdfRendererInterface;
use App\Domain\Travel\Entity\TravelExpense;
use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * 出張旅費精算書 PDF。テンプレート PDF (resources/pdf/travel_expense.pdf) に重ね書きする。
 */
final class TcpdfTravelExpensePdfRenderer implements TravelExpensePdfRendererInterface
{
    private const TEMPLATE = 'pdf/travel_expense.pdf';

    public function render(TravelExpense $expense): string
    {
        mb_internal_encoding('UTF-8');

        $pdf = new Fpdi();
        $pdf->setSourceFile(resource_path(self::TEMPLATE));
        $pdf->SetMargins(0, 0, 0);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage('A4', 'P');
        $pdf->useTemplate($pdf->importPage(1));

        $font = 'kozminproregular';
        $pdf->SetFont($font, '', 11.4);

        // 費目。左列と右列に分かれている
        $fees = [
            [24, 54, $expense->transportationFee()],   // 交通費
            [24, 88, $expense->gasFee()],              // ガソリン代
            [24, 122, $expense->dailyAllowance()],     // 日当
            [113, 54, $expense->accommodationFee()],   // 宿泊費
            [113, 88, $expense->lunchFee()],           // 昼食代
            [113, 122, $expense->dinnerFee()],         // 夕食代
        ];

        foreach ($fees as [$x, $y, $money]) {
            $pdf->SetXY($x, $y);
            $pdf->MultiCell(78, 0, $money->format());
        }

        // 合計 (内訳から導出した値)
        $pdf->SetXY(40, 149);
        $pdf->MultiCell(145, 0, $expense->totalFee()->format());

        // 出張先
        $pdf->SetXY(38, 183.5);
        $pdf->MultiCell(150, 0, $expense->destination());

        // 目的
        $pdf->SetXY(38, 190);
        $pdf->MultiCell(150, 0, $expense->purpose());

        // 年月日
        $this->drawDateParts($pdf, $expense->applyDate(), 170);
        $this->drawDateParts($pdf, $expense->dateFrom(), 210.5);
        $this->drawDateParts($pdf, $expense->dateTo(), 224.5);
        $this->drawDateParts($pdf, $expense->payDate(), 238);

        // 申請者
        $pdf->SetFont($font, '', 16);
        $pdf->SetXY(158, 264);
        $pdf->Cell(20, 0, $expense->applyPerson(), 0, 0, 'R');

        // TODO: 承認印の差し込みは移行前からコメントアウトされたまま (出張申請書と同じ)。

        return (string) $pdf->Output('biztrip.pdf', 'S');
    }

    private function drawDateParts(Fpdi $pdf, \DateTimeImmutable $date, float $y): void
    {
        $pdf->Text(38, $y, $date->format('Y'));
        $pdf->Text(55.5, $y, $date->format('m'));
        $pdf->Text(67, $y, $date->format('d'));
    }
}
