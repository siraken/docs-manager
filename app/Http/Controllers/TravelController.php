<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Travel;
use TCPDF;

class TravelController extends Controller
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
        $trips = Travel::all();
        return view('calculate/trips/index', compact('trips'));
    }

    /**
     * Create
     */
    public function create(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $travel = new Travel();
            if ($travel->fill($request->all())->save())
            {
                redirect('/trip')->with('flash_message', 'Success');
            }
        }

        return view('calculate/trips/create');
    }

    /**
     * PDF
     */
    public function pdf($id = null)
    {
        // data
        $data = Travel::find($id);

        // basic settings
        // $this->RequestHandler->respondAs('application/pdf');
        mb_internal_encoding('UTF-8');

        // config
        // $this->pdf->setSourceFile(WWW_ROOT . 'pdf/temp/apply_plan.pdf');
        $this->pdf->SetMargins(0, 0, 0);
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);

        // add page
        $this->pdf->AddPage('A4', 'P');

        // font
        $defaultFont = 'kozminproregular';
        $this->pdf->SetFont($defaultFont, '', 11.4);

        /* ----- ----- ----- ----- ----- */
        // direction
        $this->pdf->SetXY(38, 54);
        $this->pdf->MultiCell(150, 0, $data['dir']);

        // purpose
        $this->pdf->SetXY(38, 81);
        $this->pdf->MultiCell(150, 0, $data['purpose']);

        // price
        $this->pdf->SetFont($defaultFont, '', 14);
        $this->pdf->SetXY(45, 121.3);
        $this->pdf->Cell(20, 0, number_format($data['price']), 0, 0, 'R');
        $this->pdf->SetFont($defaultFont, '', 11.4);

        // date format
        $dateFrom  = $data['date_from'];
        $dateTo    = $data['date_to'];
        $applyDate = $data['apply_date'];

        // date from
        $this->pdf->Text(38, 135.7, date('Y', strtotime($dateFrom)));
        $this->pdf->Text(55.5, 135.7, date('m', strtotime($dateFrom)));
        $this->pdf->Text(67, 135.7, date('d', strtotime($dateFrom)));

        // date to
        $this->pdf->Text(38, 149.3, date('Y', strtotime($dateTo)));
        $this->pdf->Text(55.5, 149.3, date('m', strtotime($dateTo)));
        $this->pdf->Text(67, 149.3, date('d', strtotime($dateTo)));

        // apply date
        $this->pdf->Text(38, 176.5, date('Y', strtotime($applyDate)));
        $this->pdf->Text(55.5, 176.5, date('m', strtotime($applyDate)));
        $this->pdf->Text(67, 176.5, date('d', strtotime($applyDate)));

        // apply person
        $this->pdf->SetFont($defaultFont, '', 16);
        $this->pdf->SetXY(158, 203);
        $this->pdf->Cell(20, 0, $data['apply_person'], 0, 0, 'R');

        // stamp
        //$this->pdf->Image(WWW_ROOT . 'stamp/CompanyPresident.png', 179.5, 199, 15, 15);
        // $this->pdf->Image(WWW_ROOT . 'stamp/CompanyStamp.png', 179.5, 199, 15, 15);
        //$this->pdf->Image(WWW_ROOT . 'stamp/Company__.png', 179.5, 199, 15, 15);
        /* ----- ----- ----- ----- ----- */

        // color
        //$this->pdf->SetTextColor(0, 191, 255);

        // output pdf
        $this->pdf->Output(date('Y-m-d') . '.pdf');

    }
}
