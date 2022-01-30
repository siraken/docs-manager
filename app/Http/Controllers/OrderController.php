<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderHeader;
use App\Models\OrderDetail;

class OrderController extends Controller
{
    //
    public function index()
    {
        $orders = OrderHeader::all();
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
        return view('order/index', compact('orders', 'clients'));
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

            // ヘッダー部分
            $slip_header = [
                "destination" => $req['destination'],
                "responsible" => $req['responsible'],
                "honor_title" => $req['honor_title'],
                "issued_date" => $req['issued_date'],
                "exp_date" => $req['exp_date'],
                "order_no" => $req['order_no'],
                "title" => $req['title'],
                "price" => $req['totalPrice'],
                "remarks" => $req['remarks'],
                "reg_uid" => $req['reg_uid'],
                // "" => $req[''],
            ];

            $OrderHeader = new OrderHeader();
            $isSuccess = $OrderHeader->fill($slip_header)->save();
            $slip_id = $OrderHeader->id;

            // 明細部分
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
                if ($slip_body['item_name'][$i] !== NULL) {
                    $row[] = [
                        "slip_id" => $slip_id,
                        "item_name" => $slip_body['item_name'][$i],
                        "quantity" => $slip_body['qty'][$i],
                        "unit" => $slip_body['unit'][$i],
                        "cost" => $slip_body['cost'][$i],
                        "tax_id" => $slip_body['tax'][$i],
                        "price" => $slip_body['price'][$i],
                    ];
                }
            }

            $OrderDetail = new OrderDetail();
            foreach ($row as $body) {
                $OrderDetail->create($body);
            }

            if ($isSuccess)
            {
                return redirect('/order')->with('flash_message', 'Successful');
            }

        }
        return view('order/create', compact('clients'));
    }
}
