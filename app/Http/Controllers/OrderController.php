<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\OrderHeader;
use App\Models\OrderDetail;

class OrderController extends Controller
{
    //
    public function index()
    {
        $select = [
            'o.id',
            'o.responsible',
            'o.honor_title',
            'o.issued_date',
            'o.exp_date',
            'o.order_no',
            'o.price',
            'o.remarks',
            'o.is_issued',
            'o.is_deleted',
            'o.is_converted',
            'o.note',
            'c.name as destination'
        ];
        // $orders = OrderHeader::all();
        $orders = OrderHeader::select($select)
        ->from('order_headers as o')
        ->join('clients as c', 'o.destination', '=', 'c.id')
        ->get();
        print(OrderHeader::select($select)
        ->from('order_headers as o')
        ->join('clients as c', 'o.destination', '=', 'c.id')
        ->toSql());
        return view('order/index', compact('orders'));
    }

    public function create(Request $request)
    {
        $clients = Client::all();

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

    public function edit(Request $request, $id)
    {
        $clients = Client::all();

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

        $header = OrderHeader::find($id);
        $details = OrderDetail::where('slip_id', $id)->get();
        return view('order/edit', compact('clients', 'header', 'details'));
    }

    /**
     * set status
     */
    public function setStatus()
    {
        $this->autoRender = false;
        $this->request->allowMethod(['post']);
        $json = file_get_contents("php://input");
        $data = json_decode($json);

        // $estimatesTable = TableRegistry::getTableLocator()->get('EstimateHeaders');
        // $estimate = $estimatesTable->get($data->id);

        // switch($data->type) {
        //     case 'issued':
        //         $estimate->issued_flg = $data->currentStatus == 0 ? 1 : 0;
        //         break;
        //     case 'paid':
        //         switch($data->currentStatus) {
        //             case 0:
        //                 $paid_flg = 1;
        //                 break;
        //             case 1:
        //                 $paid_flg = 2;
        //                 break;
        //             case 2:
        //                 $paid_flg = 0;
        //                 break;
        //             default:
        //                 $paid_flg = 0;
        //                 break;
        //         }
        //         $estimate->paid_flg = $paid_flg;
        //         break;
        //     default: return false;
        // }

        // if ($estimatesTable->save($estimate)) {
        //     $ret = [
        //         'status' => 200
        //     ];
        // } else {
        //     $ret = [
        //         'status' => 500
        //     ];
        // }

        // echo json_encode($ret);
    }
}
