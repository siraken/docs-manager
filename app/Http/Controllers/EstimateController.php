<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EstimateHeader;
use App\Models\EstimateDetail;
use App\Models\Client;

class EstimateController extends Controller
{
    /**
     * Index
     *
     */
    public function index()
    {
        $estimates = EstimateHeader::all();
        return view('estimates/index', compact('estimates'));
    }

    /**
     * Create
     *
     */
    public function create(Request $request)
    {
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
                "estimate_no" => $req['estimate_no'],
                "title" => $req['title'],
                "subtotal_price" => $req['subtotal'],
                "tax_price" => $req['taxTotal'],
                "total_price" => $req['totalPrice'],
                "remarks" => $req['remarks'],
                "is_issued" => 0,
                "is_deleted" => 0,
                "is_converted" => 0,
            ];

            $EstimateHeader = new EstimateHeader();
            $isSuccess = $EstimateHeader->fill($slip_header)->save();
            $slip_id = $EstimateHeader->id;

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

            $EstimateDetail = new EstimateDetail();
            foreach ($row as $body) {
                $EstimateDetail->create($body);
            }

            if ($isSuccess)
            {
                return redirect('/estimates')->with([
                    'flash_message' => 'Successful',
                    'flash_status' => 'success',
                    'flash_icon' => 'check-circle-fill',
                ]);
                // return redirect('/estimates')->with([
                //     'flash_message' => '失敗しました',
                //     'flash_status' => 'danger',
                //     'flash_icon' => 'x-circle-fill',
                // ]);
            }
        }
        $clients = Client::all();
        return view('estimates/create', compact('clients'));
    }

    /**
     * Edit
     *
     */
    public function edit(Request $request, $id)
    {
        if ($request->isMethod('POST'))
        {
            $slip_header = [];
            $slip_body = [];
            $req = $request->all();

            // 最初に既存データ削除
            EstimateHeader::where('id', $id)->delete();
            EstimateDetail::where('slip_id', $id)->delete();

            // ヘッダー部分
            $slip_header = [
                "destination" => $req['destination'],
                "responsible" => $req['responsible'],
                "honor_title" => $req['honor_title'],
                "issued_date" => $req['issued_date'],
                "exp_date" => $req['exp_date'],
                "estimate_no" => $req['estimate_no'],
                "title" => $req['title'],
                "subtotal_price" => $req['subtotal'],
                "tax_price" => $req['taxTotal'],
                "total_price" => $req['totalPrice'],
                "remarks" => $req['remarks'],
                "is_issued" => $req['is_issued'],
                "is_deleted" => $req['is_deleted'],
                "is_converted" => $req['is_converted'],
                // "" => $req[''],
            ];

            $EstimateHeader = new EstimateHeader();
            $isSuccess = $EstimateHeader->fill($slip_header)->save();
            $slip_id = $EstimateHeader->id;

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

            $EstimateDetail = new EstimateDetail();
            foreach ($row as $body) {
                $EstimateDetail->create($body);
            }

            if ($isSuccess)
            {
                return redirect('/estimates')->with([
                    'flash_message' => 'Successful',
                    'flash_status' => 'success',
                    'flash_icon' => 'check-circle-fill',
                ]);
            }

        }

        $clients = Client::all();
        $header = EstimateHeader::find($id);
        $details = EstimateDetail::where('slip_id', $id)->get();
        return view('estimates/edit', compact('clients', 'header', 'details'));
    }

    /**
     * Trash
     *
     */
    public function trash()
    {
        $estimates = EstimateHeader::all();
        return view('estimates/index', compact('estimates'));
    }



}
