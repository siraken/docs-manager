<?php

namespace App\Http\Controllers;

use App\Models\Travel;
use Illuminate\Http\Request;
use App\Models\TravelExpense;
use setasign\Fpdi\Tcpdf\Fpdi;
use SplFileObject;

class TravelExpenseController extends Controller
{

    /**
     * Index
     */
    public function index()
    {
        $expenses = TravelExpense::all();
        return view('calculate/expenses/index', compact('expenses'));
    }

    /**
     * Create
     */
    public function create(Request $request)
    {
        $expense = new TravelExpense();

        if ($request->isMethod('POST'))
        {
            if ($expense->fill($request->all())->save())
            {
                return redirect('/expenses')->with([
                    'flash_message' => 'Successful',
                    'flash_status' => 'success',
                    'flash_icon' => 'check-circle-fill',
                ]);
            }
        }

        return view('calculate/expenses/form', compact('expense'));
    }

    /**
     * Edit
     */
    public function edit(Request $request, $id = null)
    {
        $expense = TravelExpense::find($id);

        if ($expense === null) {
            abort(404, 'Not Found ;(');
        }

        if ($request->isMethod('POST'))
        {
            if ($expense->fill($request->all())->save())
            {
                return redirect('/expenses')->with([
                    'flash_message' => 'Successful',
                    'flash_status' => 'success',
                    'flash_icon' => 'check-circle-fill',
                ]);
            }
        }

        return view('calculate/expenses/form', compact('expense'));
    }

    /**
     * PDF
     */
    public function pdf($id = null)
    {
        $data = TravelExpense::find($id);
        $template_path = resource_path('pdf/travel_expense.pdf');
        mb_internal_encoding('UTF-8');

        $pdf = new Fpdi();

        // config
        $pdf->setSourceFile($template_path);
        $pdf->SetMargins(0, 0, 0);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // add page
        $pdf->AddPage('A4', 'P');
        $page = $pdf->importPage(1);
        $pdf->useTemplate($page);

        // font
        $defaultFont = 'kozminproregular';
        $pdf->SetFont($defaultFont, '', 11.4);

        /* ----- ----- ----- ----- ----- */
        // transportation expenses
        $pdf->SetXY(24, 54);
        $pdf->MultiCell(78, 0, $data['trans_fee']);

        // gas fee
        $pdf->SetXY(24, 88);
        $pdf->MultiCell(78, 0, $data['gas_fee']);

        // daily allowance
        $pdf->SetXY(24, 122);
        $pdf->MultiCell(78, 0, $data['daily_pay']);

        // accommodation fee
        $pdf->SetXY(113, 54);
        $pdf->MultiCell(78, 0, $data['acm_fee']);

        // lunch fee
        $pdf->SetXY(113, 88);
        $pdf->MultiCell(78, 0, $data['lunch_fee']);

        // dinner fee
        $pdf->SetXY(113, 122);
        $pdf->MultiCell(78, 0, $data['dinner_fee']);

        // total fee
        $pdf->SetXY(40, 149);
        $pdf->MultiCell(145, 0, $data['total_fee']);

        // direction
        $pdf->SetXY(38, 183.5);
        $pdf->MultiCell(150, 0, $data['dir']);

        // purpose
        $pdf->SetXY(38, 190);
        $pdf->MultiCell(150, 0, $data['purpose']);

        // price
        $pdf->SetFont($defaultFont, '', 14);
        $pdf->SetXY(45, 121.5);
        //$pdf->Cell(20, 0, number_format($data['price']), 0, 0, 'R');
        $pdf->SetFont($defaultFont, '', 11.4);

        // date format
        $applyDate = $data['apply_date'];
        $dateFrom  = $data['date_from'];
        $dateTo    = $data['date_to'];
        $payDate   = $data['pay_date'];

        // apply date
        $pdf->Text(38, 170, date('Y', strtotime($applyDate)));
        $pdf->Text(55.5, 170, date('m', strtotime($applyDate)));
        $pdf->Text(67, 170, date('d', strtotime($applyDate)));

        // date from
        $pdf->Text(38, 210.5, date('Y', strtotime($dateFrom)));
        $pdf->Text(55.5, 210.5, date('m', strtotime($dateFrom)));
        $pdf->Text(67, 210.5, date('d', strtotime($dateFrom)));

        // date to
        $pdf->Text(38, 224.5, date('Y', strtotime($dateTo)));
        $pdf->Text(55.5, 224.5, date('m', strtotime($dateTo)));
        $pdf->Text(67, 224.5, date('d', strtotime($dateTo)));

        // pay date
        $pdf->Text(38, 238, date('Y', strtotime($payDate)));
        $pdf->Text(55.5, 238, date('m', strtotime($payDate)));
        $pdf->Text(67, 238, date('d', strtotime($payDate)));

        // apply person
        $pdf->SetFont($defaultFont, '', 16);
        $pdf->SetXY(158, 264);
        $pdf->Cell(20, 0, $data['apply_person'], 0, 0, 'R');

        // stamp
        //$pdf->Image(WWW_ROOT . 'stamp/CompanyPresident.png', 179.5, 260, 15, 15);
        // $pdf->Image(WWW_ROOT . 'stamp/CompanyStamp.png', 179.5, 260, 15, 15);
        //$pdf->Image(WWW_ROOT . 'stamp/Company__.png', 179.5, 260, 15, 15);
        /* ----- ----- ----- ----- ----- */

        // color
        //$pdf->SetTextColor(0, 191, 255);

        // output pdf
        $pdf->Output('biztrip_' . date('Ymd') . '.pdf');
    }

    /**
     * CSV import
     */
    public function csvImport(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $file = $request->file('csv');
            $headerOn = $request->header;

            if ($file->isValid())
            {

                $csv = new SplFileObject($file->getRealPath());
                $csv->setFlags(
                    SplFileObject::READ_CSV |
                    SplFileObject::READ_AHEAD |
                    SplFileObject::SKIP_EMPTY |
                    SplFileObject::DROP_NEW_LINE
                );

                $csvArray = [];
                foreach ($csv as $row) {
                    $csvArray[] = $row;
                }

                if ($headerOn)
                {
                    array_shift($csvArray);
                }

                $csv = $csvArray;
                // var_dump($csv);
                // exit;

                // TODO: もう少し良い感じのロジックがあれば直す
                foreach ($csv as $row)
                {
                    $travelExpense = new TravelExpense();
                    $travelExpense->rel_id = $row[1];
                    $travelExpense->dir = $row[2];
                    $travelExpense->purpose = $row[3];
                    $travelExpense->apply_date = $row[4];
                    $travelExpense->date_from = $row[5];
                    $travelExpense->date_to = $row[6];
                    $travelExpense->pay_date = $row[7];
                    $travelExpense->apply_person = $row[8];
                    $travelExpense->trans_fee = $row[9];
                    $travelExpense->acm_fee = $row[10];
                    $travelExpense->gas_fee = $row[11];
                    $travelExpense->dinner_fee = $row[12];
                    $travelExpense->lunch_fee = $row[13];
                    $travelExpense->daily_pay = $row[14];
                    $travelExpense->total_fee = $row[15];
                    $travelExpense->save();
                }

                return redirect('/trips')->with([
                    'flash_message' => 'Successful',
                    'flash_status' => 'success',
                    'flash_icon' => 'check-circle-fill',
                ]);

            }

            return redirect('/trips')->with([
                'flash_message' => 'Failed',
                'flash_status' => 'danger',
                'flash_icon' => 'x-circle-fill',
            ]);

        }

        return redirect('/trips')->with([
            'flash_message' => 'Failed',
            'flash_status' => 'danger',
            'flash_icon' => 'x-circle-fill',
        ]);

    }

}
