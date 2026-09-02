<?php

declare(strict_types=1);

namespace App\Infrastructure\Pdf;

use App\Application\Order\Port\OrderPdfRendererInterface;
use App\Domain\Order\Entity\Order;
use App\Domain\Setting\Entity\CompanyProfile;
use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * 発注書 PDF を座標指定で描く実装 (TCPDF / FPDI)。
 *
 * 座標は mm 単位のマジックナンバーで、レイアウトを変えたら実際に PDF を出して
 * 目視で確認すること。移行前は OrderController::pdf() が同じ描画をしたうえで
 * Output() でブラウザに直接書き出しており、テストから内容を検証できなかった。
 * ここでは文字列で返し、HTTP への載せ方はコントローラに任せる。
 */
final class TcpdfOrderPdfRenderer implements OrderPdfRendererInterface
{
    /** 明細行の高さ (mm) */
    private const CELL_HEIGHT = 6.5;

    /** 明細ヘッダーの Y 位置 (mm) */
    private const DETAIL_HEADER_Y = 96.25;

    /** 明細テーブルの左端と右端 (mm) */
    private const TABLE_LEFT = 21.0;
    private const TABLE_WIDTH = 172.0;

    /** 明細が少なくてもこの行数までは罫線を引く */
    private const MIN_ROWS = 8;

    public function render(Order $order, string $customerName, CompanyProfile $company): string
    {
        mb_internal_encoding('UTF-8');

        $pdf = new Fpdi();
        $pdf->SetMargins(0, 0, 0);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage('A4', 'P');

        // 明朝。ゴシックにするなら kozgopromedium
        $pdf->SetFont('kozminproregular', '', 11);

        $this->drawTitle($pdf);
        $this->drawAddressee($pdf, $order, $customerName);
        $this->drawTotalBox($pdf, $order);
        $this->drawIssueInfo($pdf, $order);
        $this->drawCompany($pdf, $company);
        $this->drawSummary($pdf, $order);
        $this->drawRemarks($pdf, $order);
        $this->drawDetails($pdf, $order);

        // 'S' でバイト列として受け取る (ブラウザへ直接書き出さない)
        return (string) $pdf->Output($order->orderNo()->toFileName('pdf'), 'S');
    }

    private function drawTitle(Fpdi $pdf): void
    {
        // TODO: 自社宛の発注書も同じ様式で出している。宛先によって
        //       タイトルを変えられるようにしたいという要望が残っている。
        $pdf->SetFontSize(20);
        $pdf->Text(18.5, 16.5, '発注書');
    }

    private function drawAddressee(Fpdi $pdf, Order $order, string $customerName): void
    {
        $pdf->SetFontSize(11);
        $pdf->Text(18.5, 31, $order->addresseeLine($customerName));
        $pdf->SetFontSize(9.5);
        $pdf->Text(18.5, 45, '下記の通り発注致します。');
    }

    private function drawTotalBox(Fpdi $pdf, Order $order): void
    {
        $pdf->SetFontSize(11);
        $pdf->Text(37, 55, '合計金額');
        $pdf->Text(85, 55, '円');
        $pdf->Line(29.75, 61.5, 109, 61.5);
        $pdf->SetFontSize(16);
        $pdf->SetXY(85, 53.5);
        $pdf->Cell(1, 0, $order->total()->format(), 0, 0, 'R');
    }

    private function drawIssueInfo(Fpdi $pdf, Order $order): void
    {
        $pdf->SetFontSize(9.5);
        $pdf->Text(121, 31, '注文日:');
        $pdf->SetXY(195, 31);
        $pdf->Cell(1, 0, $order->issuedDate()->format('Y年m月d日'), 0, 0, 'R');

        $pdf->Text(121, 39, '注文番号:');
        $pdf->SetXY(195, 39);
        $pdf->Cell(1, 0, (string) $order->orderNo(), 0, 0, 'R');
    }

    /**
     * 差出人欄 (ロゴ・社印・自社情報)。
     *
     * TODO: 宛先が自社の場合はこの欄と社印を印字しない、という分岐が
     *       移行前から TODO のまま残っている。自社を表す顧客 ID を
     *       設定に持たせてから実装すること。
     */
    private function drawCompany(Fpdi $pdf, CompanyProfile $company): void
    {
        $this->drawImageIfExists($pdf, $company->logoUrl(), 126, 80, 45);
        $this->drawImageIfExists($pdf, $company->companyStampUrl(), 135, 48, 23);

        $pdf->SetFontSize(9.5);

        $y = 47.0;
        $pdf->Text(121, $y, $company->name());

        $y += 5;
        $pdf->Text(121, $y, '〒' . $company->zipcode());

        foreach ($company->addressLines() as $line) {
            $y += 5;
            $pdf->Text(121, $y, $line);
        }

        $y += 5;
        $pdf->Text(121, $y, '電話: ' . $company->telNo());
    }

    private function drawSummary(Fpdi $pdf, Order $order): void
    {
        $pdf->SetFontSize(9.5);
        $pdf->Text(131, 161.75, '小計');
        $pdf->SetXY(170, 161.75);
        $pdf->Cell(20, 0, $order->subtotal()->format(), 0, 0, 'R');
        $pdf->Line(120, 168.25, 192, 168.25);

        $pdf->Text(130, 170.75, '消費税');
        $pdf->SetXY(170, 170.75);
        $pdf->Cell(20, 0, $order->tax()->format(), 0, 0, 'R');
        $pdf->Line(120, 177.25, 192, 177.25);

        $pdf->SetFontSize(12);
        $pdf->Text(126.5, 180, '合計金額');
        $pdf->SetXY(170, 180);
        $pdf->Cell(20, 0, $order->total()->format(), 0, 0, 'R');
        $pdf->Line(120, 187.25, 192, 187.25);
    }

    private function drawRemarks(Fpdi $pdf, Order $order): void
    {
        $pdf->Line(20, 195, 192, 195);
        $pdf->SetFontSize(9);
        $pdf->Text(19, 196.5, '備考欄');
        $pdf->Text(19, 201.5, (string) $order->remarks());
    }

    private function drawDetails(Fpdi $pdf, Order $order): void
    {
        // getLastH() が行の高さを返すよう、いちど空の MultiCell を出しておく
        $pdf->MultiCell(0, self::CELL_HEIGHT, '');

        $columns = [
            ['label' => '詳細', 'width' => $this->percentOfTable(52), 'align' => 'L'],
            ['label' => '数量', 'width' => $this->percentOfTable(16), 'align' => 'R'],
            ['label' => '単価', 'width' => $this->percentOfTable(16), 'align' => 'R'],
            ['label' => '金額', 'width' => $this->percentOfTable(16), 'align' => 'R'],
        ];

        // ヘッダー行 (白抜き)
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255, 255, 255);
        $this->drawRow($pdf, $columns, array_column($columns, 'label'), self::DETAIL_HEADER_Y);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFontSize(9);

        $lines = $order->lines();
        $rowCount = max(count($lines), self::MIN_ROWS);
        $y = self::DETAIL_HEADER_Y + self::CELL_HEIGHT;

        for ($i = 0; $i < $rowCount; $i++) {
            $line = $lines[$i] ?? null;

            // 1 行おきに薄いグレーを敷く
            $i % 2 === 0 ? $pdf->SetFillColor(255, 255, 255) : $pdf->SetFillColor(230, 230, 230);

            $this->drawRow($pdf, $columns, [
                $line?->itemName ?? '',
                $line === null ? '' : number_format($line->quantity) . (string) $line->unit,
                $line === null ? '' : $line->unitCost->format(),
                $line === null ? '' : $line->total()->format(),
            ], $y);

            $y += self::CELL_HEIGHT;
        }
    }

    /**
     * @param list<array{label: string, width: float, align: string}> $columns
     * @param list<string> $values
     */
    private function drawRow(Fpdi $pdf, array $columns, array $values, float $y): void
    {
        $x = self::TABLE_LEFT;

        foreach ($columns as $index => $column) {
            $isLast = $index === array_key_last($columns);

            $pdf->MultiCell(
                $column['width'],
                $pdf->getLastH(),
                $values[$index] ?? '',
                0,
                $column['align'],
                true,
                $isLast ? 0 : 1,
                $x,
                $y,
                false,
                0,
                false,
                true,
                0,
                'M',
                false,
            );

            $x += $column['width'];
        }
    }

    /** 明細テーブル幅に対する割合 (%) を mm に直す */
    private function percentOfTable(float $percent): float
    {
        return self::TABLE_WIDTH * $percent * 0.01;
    }

    /**
     * 画像を描く。ファイルが無ければ黙って飛ばす。
     * 設定にファイル名だけが入っている運用なので resources/ からの相対で解決する。
     */
    private function drawImageIfExists(Fpdi $pdf, string $path, float $x, float $y, float $width): void
    {
        if ($path === '') {
            return;
        }

        $resolved = str_starts_with($path, '/') ? $path : resource_path($path);

        if (!is_file($resolved)) {
            return;
        }

        $pdf->Image($resolved, $x, $y, $width);
    }
}
