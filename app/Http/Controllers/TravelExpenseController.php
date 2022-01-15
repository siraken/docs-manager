<?php

namespace App\Http\Controllers;

use App\Models\Travel;
use Illuminate\Http\Request;
use TCPDF;
use App\Models\TravelExpense;

class TravelExpenseController extends Controller
{
    //
    private $pdf;

    /**
     * Construct
     */
    public function __construct(TCPDF $pdf)
    {
        // コンストラクタインジェクションでTCPDFクラスをインスタンス化
        $this->pdf = $pdf;
    }

    /**
     * Index
     */
    public function index()
    {
        return view('calculate/expenses/index');
    }

    /**
     * PDF
     */
    public function pdf($id = null)
    {
        // data
        $data = TravelExpense::find($id);

        // basic settings
        // $this->RequestHandler->respondAs('application/pdf');
        mb_internal_encoding('UTF-8');

        // config
        // $this->pdf->setSourceFile(WWW_ROOT . 'pdf/temp/travel_expense.pdf');
        $this->pdf->SetMargins(0, 0, 0);
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);

        // add page
        $this->pdf->AddPage('A4', 'P');
        // $index = $this->pdf->importPage(1);
        // $this->pdf->useTemplate($index, 0, 0);

        // font
        $defaultFont = 'kozminproregular';
        $this->pdf->SetFont($defaultFont, '', 11.4);

        /* ----- ----- ----- ----- ----- */
        // transportation expenses
        $this->pdf->SetXY(24, 54);
        $this->pdf->MultiCell(78, 0, $data['trans_fee']);

        // gas fee
        $this->pdf->SetXY(24, 88);
        $this->pdf->MultiCell(78, 0, $data['gas_fee']);

        // daily allowance
        $this->pdf->SetXY(24, 122);
        $this->pdf->MultiCell(78, 0, $data['daily_pay']);

        // accommodation fee
        $this->pdf->SetXY(113, 54);
        $this->pdf->MultiCell(78, 0, $data['acm_fee']);

        // lunch fee
        $this->pdf->SetXY(113, 88);
        $this->pdf->MultiCell(78, 0, $data['lunch_fee']);

        // dinner fee
        $this->pdf->SetXY(113, 122);
        $this->pdf->MultiCell(78, 0, $data['dinner_fee']);

        // total fee
        $this->pdf->SetXY(40, 149);
        $this->pdf->MultiCell(145, 0, $data['total_fee']);

        // direction
        $this->pdf->SetXY(38, 183.5);
        $this->pdf->MultiCell(150, 0, $data['dir']);

        // purpose
        $this->pdf->SetXY(38, 190);
        $this->pdf->MultiCell(150, 0, $data['purpose']);

        // price
        $this->pdf->SetFont($defaultFont, '', 14);
        $this->pdf->SetXY(45, 121.5);
        //$this->pdf->Cell(20, 0, number_format($data['price']), 0, 0, 'R');
        $this->pdf->SetFont($defaultFont, '', 11.4);

        // date format
        $applyDate = $data['apply_date'];
        $dateFrom  = $data['date_from'];
        $dateTo    = $data['date_to'];
        $payDate   = $data['pay_date'];

        // apply date
        $this->pdf->Text(38, 170, date('Y', strtotime($applyDate)));
        $this->pdf->Text(55.5, 170, date('m', strtotime($applyDate)));
        $this->pdf->Text(67, 170, date('d', strtotime($applyDate)));

        // date from
        $this->pdf->Text(38, 210.5, date('Y', strtotime($dateFrom)));
        $this->pdf->Text(55.5, 210.5, date('m', strtotime($dateFrom)));
        $this->pdf->Text(67, 210.5, date('d', strtotime($dateFrom)));

        // date to
        $this->pdf->Text(38, 224.5, date('Y', strtotime($dateTo)));
        $this->pdf->Text(55.5, 224.5, date('m', strtotime($dateTo)));
        $this->pdf->Text(67, 224.5, date('d', strtotime($dateTo)));

        // pay date
        $this->pdf->Text(38, 238, date('Y', strtotime($payDate)));
        $this->pdf->Text(55.5, 238, date('m', strtotime($payDate)));
        $this->pdf->Text(67, 238, date('d', strtotime($payDate)));

        // apply person
        $this->pdf->SetFont($defaultFont, '', 16);
        $this->pdf->SetXY(158, 264);
        $this->pdf->Cell(20, 0, $data['apply_person'], 0, 0, 'R');

        // stamp
        //$this->pdf->Image(WWW_ROOT . 'stamp/CompanyPresident.png', 179.5, 260, 15, 15);
        // $this->pdf->Image(WWW_ROOT . 'stamp/CompanyStamp.png', 179.5, 260, 15, 15);
        //$this->pdf->Image(WWW_ROOT . 'stamp/Company__.png', 179.5, 260, 15, 15);
        /* ----- ----- ----- ----- ----- */

        // color
        //$this->pdf->SetTextColor(0, 191, 255);

        // output pdf
        $this->pdf->Output('biztrip_' . date('Ymd') . '.pdf');
    }
}
