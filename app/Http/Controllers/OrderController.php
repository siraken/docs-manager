<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\OrderHeader;
use App\Models\OrderDetail;
use setasign\Fpdi\Tcpdf\Fpdi;

class OrderController extends Controller
{
    public function __construct()
    {
        mb_internal_encoding('UTF-8');
    }

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

        if ($request->isMethod('POST'))
        {
            $slip_header = [];
            $slip_body = [];
            $req = $request->all();

            // 最初に既存データ削除
            OrderHeader::where('id', $id)->delete();
            OrderDetail::where('slip_id', $id)->delete();

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

        $clients = Client::all();
        $header = OrderHeader::find($id);
        $details = OrderDetail::where('slip_id', $id)->get();
        return view('order/edit', compact('clients', 'header', 'details'));
    }

    /**
     * PDF
     */
    public function pdf($id = null)
    {
        $template = false;

        // データ取得
        $header = OrderHeader::find($id);
        $details = OrderDetail::where('slip_id', $id)->get();

        // FPDI
        $pdf = new Fpdi();

        // 設定
        if ($template) {
            $pdf->setSourceFile(resource_path('pdf/example.pdf'));
        }
        $pdf->SetMargins(0, 0, 0);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // ページ追加
        $pdf->AddPage('A4', 'P');
        if ($template) {
            $page = $pdf->importPage(1);
            $pdf->useTemplate($page);
        }

        // デフォルトフォント
        $defaultFont = 'kozminproregular';
        // $defaultFont = 'kozgopromedium';
        // $defaultFont = '';

        /* ヘッダー */
        // タイトル
        $pdf->SetFont($defaultFont, '', 20);
        $pdf->Text(18.5, 16.5, '発注書');

        // 宛先
        $pdf->SetFont($defaultFont, '', 11);
        $pdf->Text(18.5, 31, $header['destination'].$header['honor_title']);
        $pdf->SetFont($defaultFont, '', 9.5);
        $pdf->Text(18.5, 45, '下記の通り発注致します。');

        // 合計金額
        $pdf->SetFont($defaultFont, '', 11);
        $pdf->Text(43.5, 55, '合計金額');
        $pdf->Text(85, 55, '円');
        $pdf->Line(29.75, 61.5, 109, 61.5);
        $pdf->SetFont($defaultFont, '', 16);
        $pdf->SetXY(59.5, 53);
        $pdf->Cell(20, 0, number_format($header['price']), 0, 0, 'R');

        // 日付
        $pdf->SetFont($defaultFont, '', 9.5);
        $pdf->Text(121, 31, '注文日:');
        $pdf->Text(170, 31, date('Y年m月d日', strtotime($header['issued_date'])));

        // 発注書番号
        $pdf->SetFont($defaultFont, '', 9.5);
        $pdf->Text(121, 39, '注文番号');
        $pdf->Text(170, 39, $header['order_no']);

        // ロゴと印鑑
        // TODO: destinationが自社宛の場合は印字しない
        $pdf->Image(resource_path('img/Logo.png'), 126, 78, 45);
        $pdf->Image(resource_path('img/CompanyStamp.png'), 135, 48, 23);

        // 自社情報
        // TODO: destinationが自社宛の場合は印字しない
        $pdf->SetFont($defaultFont, '', 9.5);
        $pdf->Text(121, 47, 'Novalumo合同会社');
        $pdf->Text(121, 52, '〒000-000');
        $pdf->Text(121, 57, '〇〇県〇〇市１行目');
        $pdf->Text(121, 62, '２行目001号室');
        $pdf->Text(121, 67, '電話: 000-0000-0000');

        // apply date
        // $pdf->Text(130, 60, date('Y', strtotime($applyDate)));
        // $pdf->Text(150, 60, date('m', strtotime($applyDate)));
        // $pdf->Text(170, 60, date('d', strtotime($applyDate)));

        // 合計
        // TODO: セルにする
        $pdf->SetFont($defaultFont, '', 9.5);
        $pdf->Text(131, 161.75, '小計');
        // $pdf->Text(170, 161.75, $header['price']);
        $pdf->SetXY(170, 161.75);
        $pdf->Cell(20, 0, number_format($header['price']), 0, 0, 'R');
        $pdf->Text(130, 170.5, '消費税');
        // $pdf->Text(170, 170.5, $header['price']);
        $pdf->SetXY(170, 170.5);
        $pdf->Cell(20, 0, number_format($header['price']), 0, 0, 'R');

        $pdf->SetFont($defaultFont, '', 12);
        $pdf->Text(126.5, 180, '合計金額');
        // $pdf->Text(168, 180, $header['price']);
        $pdf->SetXY(168, 180);
        $pdf->Cell(20, 0, number_format($header['price']), 0, 0, 'R');

        // 備考欄
        $pdf->Line(20, 195, 192, 195);
        $pdf->SetFont($defaultFont, '', 9);
        $pdf->Text(19, 196.5, '備考欄');
        $pdf->Text(19, 201.5, $header['remarks']);

        /* 明細 */
        // 明細開始位置
        $pdf->SetFont($defaultFont, '', 9);
        $detail_y = 104;
        // 明細行ループ
        foreach ($details as $i => $d) {
            // 詳細
            $pdf->Text(21, $detail_y, $d['item_name']);
            // 数量・単位
            $pdf->Text(130, $detail_y, $d['quantity'].$d['unit']);
            // 単価
            $pdf->Text(153, $detail_y, $d['cost']);
            // 金額
            $pdf->Text(178, $detail_y, $d['price']);
            $detail_y += 7;
        }

        // 出力
        // TODO: 発注書番号にする
        $pdf->Output(date('Ymd-001') . '.pdf');

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
