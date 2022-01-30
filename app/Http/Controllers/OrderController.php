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
        if ($request->isMethod('POST'))
        {
            $slip_header = [];
            $slip_body = [];
            $req = $request->all();
            // var_dump($req);

            // 明細部分だけ取り出し
            $slip_body = [
                "item_name" => $req['item_name'],
                "qty" => $req['qty'],
                "unit" => $req['unit'],
                "cost" => $req['cost'],
                "tax" => $req['tax'],
                "price" => $req['price'],
            ];

            // 明細データ作成
            $row = [];
            for ($i = 0; $i < count($slip_body["item_name"]); $i++) {
                $row[] = [
                    "item_name" => $slip_body['item_name'][$i],
                    "qty" => $slip_body['qty'][$i],
                    "unit" => $slip_body['unit'][$i],
                    "cost" => $slip_body['cost'][$i],
                    "tax" => $slip_body['tax'][$i],
                    "price" => $slip_body['price'][$i],
                ];
            }
            var_dump($row);

            exit;
            // $something = new something();
            // if ($something->fill($request->all())->save())
            // {
            //     return redirect('/something')->with('flash_message', 'Successful');
            // }
        }
        return view('order/create', compact('clients'));
    }
}
