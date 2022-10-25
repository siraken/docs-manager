<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderHeader;
use App\Models\OrderDetail;
use App\Lib\Common;
use App\Models\Customer;
use setasign\Fpdi\Tcpdf\Fpdi;

class OrderController extends Controller
{
    public function __construct()
    {
        mb_internal_encoding('UTF-8');
    }

    /**
     * 一覧
     */
    public function index()
    {
        $select = [
            'o.id',
            'o.responsible',
            'o.honor_title',
            'o.issued_date',
            'o.exp_date',
            'o.order_no',
            'o.title',
            'o.total_price',
            'o.remarks',
            'o.is_issued',
            'o.is_ordered',
            'o.is_deleted',
            'o.is_converted',
            'o.note',
        ];
        $orders = OrderHeader::select($select)
            ->from('order_headers as o')
            ->where('o.is_deleted', '!=', '1')
            ->get();
        return view('orders/index', compact('orders'));
    }

    /**
     * ゴミ箱: 削除済み一覧
     */
    public function trash()
    {
        $select = [
            'o.id',
            'o.responsible',
            'o.honor_title',
            'o.issued_date',
            'o.exp_date',
            'o.order_no',
            'o.title',
            'o.total_price',
            'o.remarks',
            'o.is_issued',
            'o.is_ordered',
            'o.is_deleted',
            'o.is_converted',
            'o.note',
            'c.name as customer_id'
        ];
        $orders = OrderHeader::select($select)
            ->from('order_headers as o')
            ->join('clients as c', 'o.customer_id', '=', 'c.id')
            ->where('o.is_deleted', '=', '1')
            ->get();
        return view('orders/trash', compact('orders'));
    }

    /**
     * 新規作成
     */
    public function create(Request $request)
    {

        $customers = Customer::all();

        if ($request->isMethod('POST')) {
            $slip_header = [];
            $slip_body = [];
            $req = $request->all();

            // ヘッダー部分
            $slip_header = [
                "customer_id" => $req['customer_id'],
                "responsible" => $req['responsible'],
                "honor_title" => $req['honor_title'],
                "issued_date" => $req['issued_date'],
                "exp_date" => $req['exp_date'],
                "order_no" => $req['order_no'],
                "title" => $req['title'],
                "subtotal_price" => $req['subtotal'],
                "tax_price" => $req['taxTotal'],
                "total_price" => $req['totalPrice'],
                "remarks" => $req['remarks'],
                "is_issued" => 0,
                "is_ordered" => 0,
                "is_deleted" => 0,
                "is_converted" => 0,
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

            if ($isSuccess) {
                return redirect('/orders')->with([
                    'flash_message' => 'Successful',
                    'flash_status' => 'success',
                    'flash_icon' => 'check-circle-fill',
                ]);
                // return redirect('/orders')->with([
                //     'flash_message' => '失敗しました',
                //     'flash_status' => 'danger',
                //     'flash_icon' => 'x-circle-fill',
                // ]);
            }
        }
        return view('orders/form', compact('customers'));
    }

    /**
     * 編集
     */
    public function edit(Request $request, $id)
    {

        $customers = Customer::all();

        if ($request->isMethod('POST')) {
            $slip_header = [];
            $slip_body = [];
            $req = $request->all();

            // 最初に既存データ削除
            OrderHeader::where('id', $id)->delete();
            OrderDetail::where('slip_id', $id)->delete();

            // ヘッダー部分
            $slip_header = [
                "customer_id" => $req['customer_id'],
                "responsible" => $req['responsible'],
                "honor_title" => $req['honor_title'],
                "issued_date" => $req['issued_date'],
                "exp_date" => $req['exp_date'],
                "order_no" => $req['order_no'],
                "title" => $req['title'],
                "subtotal_price" => $req['subtotal'],
                "tax_price" => $req['taxTotal'],
                "total_price" => $req['totalPrice'],
                "remarks" => $req['remarks'],
                "is_issued" => $req['is_issued'],
                "is_ordered" => $req['is_ordered'],
                "is_deleted" => $req['is_deleted'],
                "is_converted" => $req['is_converted'],
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

            if ($isSuccess) {
                return redirect('/orders')->with([
                    'flash_message' => 'Successful',
                    'flash_status' => 'success',
                    'flash_icon' => 'check-circle-fill',
                ]);
            }
        }

        $header = OrderHeader::find($id);
        $details = OrderDetail::where('slip_id', $id)->get();
        return view('orders/form', compact('customers', 'header', 'details'));
    }

    /**
     * PDF生成
     */
    public function pdf($id = null)
    {
        // データ取得
        $header = OrderHeader::find($id);
        $details = OrderDetail::where('slip_id', $id)->get();
        $customer = Customer::find($header->customer_id);

        // 呼び出し
        $pdf = new Fpdi();
        $Common = new Common();

        // 設定
        $pdf->SetMargins(0, 0, 0);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // ページ追加
        $pdf->AddPage('A4', 'P');

        // デフォルトフォント
        $defaultFont = 'kozminproregular';
        // $defaultFont = 'kozgopromedium';
        $pdf->SetFont($defaultFont, '', 11);

        // タイトル
        // TODO: 自社宛の場合などに備えてタイトル変更可能にする
        $pdf->SetFontSize(20);
        $pdf->Text(18.5, 16.5, '発注書');

        // 宛先
        $pdf->SetFontSize(11);
        $pdf->Text(18.5, 31, $customer->name . ' ' . $header['responsible'] . ' ' . $header['honor_title']);
        $pdf->SetFontSize(9.5);
        $pdf->Text(18.5, 45, '下記の通り発注致します。');

        // 合計金額
        $pdf->SetFontSize(11);
        $pdf->Text(37, 55, '合計金額');
        $pdf->Text(85, 55, '円');
        $pdf->Line(29.75, 61.5, 109, 61.5);
        $pdf->SetFontSize(16);
        $pdf->SetXY(85, 53.5);
        $pdf->Cell(1, 0, number_format($header['total_price']), 0, 0, 'R');

        // 日付
        $pdf->SetFontSize(9.5);
        $pdf->Text(121, 31, '注文日:');
        $pdf->SetXY(195, 31);
        $pdf->Cell(1, 0, date('Y年m月d日', strtotime($header['issued_date'])), 0, 0, 'R');

        // 発注書番号
        $pdf->SetFontSize(9.5);
        $pdf->Text(121, 39, '注文番号:');
        $pdf->SetXY(195, 39);
        $pdf->Cell(1, 0, $header['order_no'], 0, 0, 'R');

        // ロゴと印鑑
        // TODO: customer_idが自社宛の場合は印字しない
        $pdf->Image(resource_path('img/Logo.png'), 126, 80, 45);
        $pdf->Image(resource_path('img/CompanyStamp.png'), 135, 48, 23);

        // 自社情報
        // TODO: customer_idが自社宛の場合は印字しない
        // TODO: 自社設定をマスタから取ってくる
        $pdf->SetFontSize(9.5);
        $pdf->Text(121, 47, 'Novalumo合同会社');
        $pdf->Text(121, 52, '〒000-000');
        $pdf->Text(121, 57, '〇〇県〇〇市１行目');
        $pdf->Text(121, 62, '２行目001号室');
        $pdf->Text(121, 67, '電話: 000-0000-0000');

        // 小計
        $pdf->SetFontSize(9.5);
        $pdf->Text(131, 161.75, '小計');
        $pdf->SetXY(170, 161.75);
        $pdf->Cell(20, 0, number_format($header['subtotal_price']), 0, 0, 'R');
        $pdf->Line(120, 168.25, 192, 168.25);
        // 消費税
        $pdf->Text(130, 170.75, '消費税');
        $pdf->SetXY(170, 170.75);
        $pdf->Cell(20, 0, number_format($header['tax_price']), 0, 0, 'R');
        $pdf->Line(120, 177.25, 192, 177.25);
        // 合計金額
        $pdf->SetFontSize(12);
        $pdf->Text(126.5, 180, '合計金額');
        $pdf->SetXY(170, 180);
        $pdf->Cell(20, 0, number_format($header['total_price']), 0, 0, 'R');
        $pdf->Line(120, 187.25, 192, 187.25);

        // 備考欄
        $pdf->Line(20, 195, 192, 195);
        $pdf->SetFontSize(9);
        $pdf->Text(19, 196.5, '備考欄');
        $pdf->Text(19, 201.5, $header['remarks']);

        // セル高さ合わせ
        $CellHeight = 6.5;
        $pdf->MultiCell(0, $CellHeight, '');

        // 明細ヘッダーY位置
        $detail_header_y = 96.25;
        // 明細開始初期位置
        $detail_y = $detail_header_y + $CellHeight;
        // 横幅
        $maxWidth = 192 - 20;
        // 明細ヘッダー
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->MultiCell($Common->calcPer($maxWidth, 52), $pdf->getLastH(), '詳細', 0, 'L', true, 1, 21, $detail_header_y, false, 0, false, true, 0, 'M', false);
        $pdf->MultiCell($Common->calcPer($maxWidth, 16), $pdf->getLastH(), '数量', 0, 'R', true, 1, (21 + $Common->calcPer($maxWidth, 52)), $detail_header_y, false, 0, false, true, 0, 'M', false);
        $pdf->MultiCell($Common->calcPer($maxWidth, 16), $pdf->getLastH(), '単価', 0, 'R', true, 1, (21 + $Common->calcPer($maxWidth, 52) + $Common->calcPer($maxWidth, 16)), $detail_header_y, false, 0, false, true, 0, 'M', false);
        $pdf->MultiCell($Common->calcPer($maxWidth, 16), $pdf->getLastH(), '金額', 0, 'R', true, 0, (21 + $Common->calcPer($maxWidth, 52) + $Common->calcPer($maxWidth, 16) + $Common->calcPer($maxWidth, 16)), $detail_header_y, false, 0, false, true, 0, 'M', false);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFontSize(9);

        // 明細行ループ
        $loopCount = count($details) < 8 ? 8 : count($details); // 最低8回回す
        for ($i = 0; $i < $loopCount; $i++) {

            $d = isset($details[$i]) ? $details[$i] : [
                'item_name' => NULL,
                'quantity' => NULL,
                'unit' => NULL,
                'cost' => NULL,
                'price' => NULL,
            ];

            // 背景色設定
            $i % 2 === 0 ? $pdf->SetFillColor(255, 255, 255) : $pdf->SetFillColor(230, 230, 230);

            // 詳細
            $pdf->MultiCell($Common->calcPer($maxWidth, 52), $pdf->getLastH(), $d['item_name'], 0, 'L', true, 1, 21, $detail_y, false, 0, false, true, 0, 'M', false);
            // 数量・単位
            $pdf->MultiCell($Common->calcPer($maxWidth, 16), $pdf->getLastH(), $d['quantity'] !== NULL ? number_format($d['quantity']) . $d['unit'] : '', 0, 'R', true, 1, (21 + $Common->calcPer($maxWidth, 52)), $detail_y, false, 0, false, true, 0, 'M', false);
            // 単価
            $pdf->MultiCell($Common->calcPer($maxWidth, 16), $pdf->getLastH(), $d['cost'] !== NULL ? number_format($d['cost']) : '', 0, 'R', true, 1, (21 + $Common->calcPer($maxWidth, 52) + $Common->calcPer($maxWidth, 16)), $detail_y, false, 0, false, true, 0, 'M', false);
            // 金額
            $pdf->MultiCell($Common->calcPer($maxWidth, 16), $pdf->getLastH(), $d['price'] !== NULL ? number_format($d['price']) : '', 0, 'R', true, 0, (21 + $Common->calcPer($maxWidth, 52) + $Common->calcPer($maxWidth, 16) + $Common->calcPer($maxWidth, 16)), $detail_y, false, 0, false, true, 0, 'M', false);

            // 改行
            $detail_y += $CellHeight;
        }

        // 出力
        $pdf->Output($header['order_no'] . '.pdf');
    }

    /**
     * CSVエクスポート（バックアップ用）
     */
    public function csv($id = null)
    {
        $header = OrderHeader::find($id);
        $details = OrderDetail::where('slip_id', '=', $id)->get();

        $header = json_decode(json_encode($header), true);
        $details = json_decode(json_encode($details), true);

        $headerColumns = array_keys(json_decode(json_encode($header), true));
        $detailColumns = array_keys(json_decode(json_encode($details[0]), true));
        $csv_header = array_merge($headerColumns, $detailColumns);

        $filename = './' . $header['order_no'] . '.csv';

        // ファイルを開く
        $fp = fopen($filename, 'w');
        fputcsv($fp, $csv_header);
        // 1行ずつ配列の内容をファイルに書き込む
        foreach ($details as $fields) {
            fputcsv($fp, $fields);
        }
        // ファイルを閉じる
        fclose($fp);

        // HTTPヘッダ
        header("Content-Type: application/octet-stream");
        header('Content-Length: ' . filesize($filename));
        header('Content-Disposition: attachment; filename=test.csv');
        readfile($filename);
        unlink($filename);
    }

    /**
     * ごみ箱に入れる
     */
    public function delete($id = null)
    {
        $order = OrderHeader::find($id);
        $order->is_deleted = 1;

        if ($order->save()) {
            return redirect('/orders')->with([
                'flash_message' => 'Successful',
                'flash_status' => 'success',
                'flash_icon' => 'check-circle-fill',
            ]);
        }
    }

    /**
     * ごみ箱から戻す
     */
    public function restore($id = null)
    {
        $order = OrderHeader::find($id);
        $order->is_deleted = 0;

        if ($order->save()) {
            return redirect('/orders')->with([
                'flash_message' => 'Successful',
                'flash_status' => 'success',
                'flash_icon' => 'check-circle-fill',
            ]);
        }
    }

    /**
     * ステータス変更
     */
    public function setStatus()
    {
        $json = file_get_contents("php://input");
        $data = json_decode($json);
        $Order = OrderHeader::find($data->id);

        switch ($data->type) {
            case 'issued':
                $Order->is_issued = $data->currentStatus === 0 ? 1 : 0;
                break;
            case 'ordered':
                switch ($data->currentStatus) {
                    case 0:
                        $is_ordered = 1;
                        break;
                    case 1:
                        $is_ordered = 2;
                        break;
                    case 2:
                        $is_ordered = 0;
                        break;
                    default:
                        $is_ordered = 0;
                        break;
                }
                $Order->is_ordered = $is_ordered;
                break;
            default:
                return false;
        }

        if ($Order->save()) {
            $ret = [
                'status' => 200
            ];
        } else {
            $ret = [
                'status' => 500
            ];
        }

        echo json_encode($ret);
    }
}
