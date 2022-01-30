<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    //
    public function index()
    {
        $estimates = [];
        $clients = [
            [
                "name" => "Novalumo合同会社",
                "ceo" => "白澤賢斗"
            ],
            [
                "name" => "ルーモ株式会社",
                "ceo" => "鈴木"
            ]
        ];
        return view('order/index', compact('estimates', 'clients'));
    }

    public function create(Request $request)
    {
        $clients = [
            [
                "id" => 1,
                "name" => "Novalumo合同会社",
                "ceo" => "白澤賢斗"
            ],
            [
                "id" => 2,
                "name" => "ルーモ株式会社",
                "ceo" => "鈴木"
            ]
        ];
        // if ($request->isMethod('POST'))
        // {
        //     $something = new something();
        //     if ($something->fill($request->all())->save())
        //     {
        //         return redirect('/something')->with('flash_message', 'Successful');
        //     }
        // }
        return view('order/create', compact('clients'));
    }
}
