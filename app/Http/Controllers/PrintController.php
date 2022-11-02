<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use TCPDF;

/**
 * 印刷関連コントローラー
 */
class PrintController extends Controller
{
    private $pdf; // インスタンス変数を宣言
    /**
     * コンストラクター
     */
    public function __construct(TCPDF $pdf)
    {
        // コンストラクタインジェクションでTCPDFクラスをインスタンス化
        $this->pdf = $pdf;
    }

    /**
     * 名刺
     */
    public function bizcard()
    {
    }

    /**
     * 宛名印刷
     * FIXME: This is From CakePHP
     *
     * @param Request $request
     * @param int $id
     */
    public function envelope($id = null, $type = null)
    {
        // data
        $data = $this->Clients->get($id);
        $paper_size = [120, 260];
        // $paper_size = [120, 235];
        // $paper_size = [357, 683]; // px

        // plus 71

        // config
        mb_internal_encoding('UTF-8');
        $this->RequestHandler->respondAs('application/pdf');
        $this->pdf->setSourceFile(WWW_ROOT . 'pdf/temp/envelope/n3.pdf');
        $this->pdf->SetMargins(0, 0, 0);
        $this->pdf->setPageUnit('mm');
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);

        // add page
        $this->pdf->AddPage('P', $paper_size);
        $this->pdf->useTemplate($this->pdf->importPage(1), 0, 0);

        // font
        $defaultFont = 'kozgopromedium';
        $baseSize = 11.4;

        // position
        $sx = 25;
        $sy = 58;

        /* ----- ----- ----- ----- ----- */
        // zipcode
        if ($type === null) {
            $this->pdf->SetFont($defaultFont, '', $baseSize + 2);
            $this->pdf->Text($sx, $sy - 5, '〒' . $data['zipcode']);
        } else {
            $this->pdf->SetFont($defaultFont, '', $baseSize + 7.6);
            $this->pdf->Text(65, $sy - 13, implode(
                '  ',
                str_split(str_replace('-', '', $data['zipcode']), 1)
            ));
        }

        $this->pdf->SetFont($defaultFont, '', $baseSize);

        // address
        $this->pdf->SetXY($sx, $sy + 7);
        $this->pdf->SetFont($defaultFont, '', $baseSize);
        $this->pdf->MultiCell(0, 0, $data['address']);

        // name
        // $this->pdf->SetXY(24, 54);
        $this->pdf->SetFont($defaultFont, '', $baseSize + 2);
        $this->pdf->Text($sx, $sy + 16, $data['name'] . ' ' . $data['title']);

        // output pdf
        $this->pdf->Output('biztrip_' . date('Ymd') . '.pdf');
    }

    /**
     *
     */
    public function func()
    {
    }

    public function estimate()
    {
        // フォント、スタイル、サイズ をセット
        $this->pdf->setFont('kozminproregular', '', 10);
        // ページを追加
        $this->pdf->addPage();
        $this->pdf->Text(38, 238, 'HELLO');
        // 出力の指定です、ファイル名、拡張子、Dはダウンロードを意味します。
        $this->pdf->output('test' . '.pdf', 'D');
        return;
    }
}
