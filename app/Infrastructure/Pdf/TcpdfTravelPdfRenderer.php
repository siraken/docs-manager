<?php

declare(strict_types=1);

namespace App\Infrastructure\Pdf;

use App\Application\Travel\Port\TravelPdfRendererInterface;
use App\Domain\Travel\Entity\Travel;
use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * 出張申請書 PDF。テンプレート PDF (resources/pdf/apply_plan.pdf) に重ね書きする。
 * 座標は mm 単位のマジックナンバー。
 */
final class TcpdfTravelPdfRenderer implements TravelPdfRendererInterface
{
    private const TEMPLATE = 'pdf/apply_plan.pdf';

    public function render(Travel $travel): string
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

        // 出張先
        $pdf->SetXY(38, 54);
        $pdf->MultiCell(150, 0, $travel->destination());

        // 目的
        $pdf->SetXY(38, 81);
        $pdf->MultiCell(150, 0, $travel->purpose());

        // 金額
        $pdf->SetFont($font, '', 14);
        $pdf->SetXY(45, 121.3);
        $pdf->Cell(20, 0, $travel->price()->format(), 0, 0, 'R');
        $pdf->SetFont($font, '', 11.4);

        // 年月日はテンプレートの枠に合わせて 3 つに割って書く
        $this->drawDateParts($pdf, $travel->dateFrom(), 135.7);
        $this->drawDateParts($pdf, $travel->dateTo(), 149.3);
        $this->drawDateParts($pdf, $travel->applyDate(), 176.5);

        // 申請者
        $pdf->SetFont($font, '', 16);
        $pdf->SetXY(158, 203);
        $pdf->Cell(20, 0, $travel->applyPerson(), 0, 0, 'R');

        // TODO: 承認印 (社印 / 代表者印) の差し込みは移行前からコメントアウトされたまま。
        //       押印欄をどう運用するか決まったら CompanyProfile の印影 URL を使って描くこと。

        return (string) $pdf->Output('trip.pdf', 'S');
    }

    private function drawDateParts(Fpdi $pdf, \DateTimeImmutable $date, float $y): void
    {
        $pdf->Text(38, $y, $date->format('Y'));
        $pdf->Text(55.5, $y, $date->format('m'));
        $pdf->Text(67, $y, $date->format('d'));
    }
}
