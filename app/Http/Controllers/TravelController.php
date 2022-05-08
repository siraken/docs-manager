<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Travel;
use setasign\Fpdi\Tcpdf\Fpdi;
use SplFileObject;

class TravelController extends Controller
{

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
        $travel = new Travel();

        if ($request->isMethod('POST'))
        {
            if ($travel->fill($request->all())->save())
            {
                return redirect('/trips')->with([
                    'flash_message' => 'Successful',
                    'flash_status' => 'success',
                    'flash_icon' => 'check-circle-fill',
                ]);
            }
        }

        return view('calculate/trips/form', compact('travel'));
    }

    /**
     * PDF
     */
    public function pdf($id = null)
    {
        $data = Travel::find($id);
        $template_path = resource_path('pdf/apply_plan.pdf');
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
        // direction
        $pdf->SetXY(38, 54);
        $pdf->MultiCell(150, 0, $data['dir']);

        // purpose
        $pdf->SetXY(38, 81);
        $pdf->MultiCell(150, 0, $data['purpose']);

        // price
        $pdf->SetFont($defaultFont, '', 14);
        $pdf->SetXY(45, 121.3);
        $pdf->Cell(20, 0, number_format($data['price']), 0, 0, 'R');
        $pdf->SetFont($defaultFont, '', 11.4);

        // date format
        $dateFrom  = $data['date_from'];
        $dateTo    = $data['date_to'];
        $applyDate = $data['apply_date'];

        // date from
        $pdf->Text(38, 135.7, date('Y', strtotime($dateFrom)));
        $pdf->Text(55.5, 135.7, date('m', strtotime($dateFrom)));
        $pdf->Text(67, 135.7, date('d', strtotime($dateFrom)));

        // date to
        $pdf->Text(38, 149.3, date('Y', strtotime($dateTo)));
        $pdf->Text(55.5, 149.3, date('m', strtotime($dateTo)));
        $pdf->Text(67, 149.3, date('d', strtotime($dateTo)));

        // apply date
        $pdf->Text(38, 176.5, date('Y', strtotime($applyDate)));
        $pdf->Text(55.5, 176.5, date('m', strtotime($applyDate)));
        $pdf->Text(67, 176.5, date('d', strtotime($applyDate)));

        // apply person
        $pdf->SetFont($defaultFont, '', 16);
        $pdf->SetXY(158, 203);
        $pdf->Cell(20, 0, $data['apply_person'], 0, 0, 'R');

        // stamp
        //$pdf->Image(WWW_ROOT . 'stamp/CompanyPresident.png', 179.5, 199, 15, 15);
        // $pdf->Image(WWW_ROOT . 'stamp/CompanyStamp.png', 179.5, 199, 15, 15);
        //$pdf->Image(WWW_ROOT . 'stamp/Company__.png', 179.5, 199, 15, 15);
        /* ----- ----- ----- ----- ----- */

        // color
        //$pdf->SetTextColor(0, 191, 255);

        // output pdf
        $pdf->Output(date('Y-m-d') . '.pdf');

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

                // TODO: もう少し良い感じのロジックがあれば直す
                foreach ($csv as $row)
                {
                    $travel = new Travel();
                    $travel->rel_id = $row[1];
                    $travel->dir = $row[2];
                    $travel->purpose = $row[3];
                    $travel->price = $row[4];
                    $travel->date_from = $row[5];
                    $travel->date_to = $row[6];
                    $travel->apply_date = $row[7];
                    $travel->apply_person = $row[8];
                    $travel->save();
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
                'flash_icon' => 'times-circle-fill',
            ]);

        }

        return redirect('/trips')->with([
            'flash_message' => 'Failed',
            'flash_status' => 'danger',
            'flash_icon' => 'times-circle-fill',
        ]);

    }
}
