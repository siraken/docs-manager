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
     *
     */
    public function func()
    {

    }

    public function estimate()
    {
        // フォント、スタイル、サイズ をセット
        $this->pdf->setFont('kozminproregular','',10);
        // ページを追加
        $this->pdf->addPage();
        $this->pdf->Text(38, 238, 'HELLO');
        // 出力の指定です、ファイル名、拡張子、Dはダウンロードを意味します。
        $this->pdf->output('test' . '.pdf', 'D');
        return;
    }
}
