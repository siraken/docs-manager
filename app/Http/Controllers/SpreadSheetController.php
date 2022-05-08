<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SpreadSheet;

class SpreadSheetController extends Controller
{
    public function store()
    {
        $spread_sheet = new SpreadSheet();

        $insert_data = [
            'hoge' => 'Test',
            'huga' => 12345,
            'foo'  => true
        ];

        $spread_sheet->insert_spread_sheet($insert_data);

        return response('Test data are stored successfully.', 200);
    }
}
