<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Work;
use Faker\Guesser\Name;

class WorkController extends Controller
{
    /**
     * 一覧表示
     */
    public function index()
    {
        $works = Work::all();
        foreach ($works as $work) {
            // FIXME:
            $work->client_id = "あいうえお";
            switch ($work->status) {
                case $work->status === 0:
                    $work->status = '未着手';
                    break;
                case $work->status === 1:
                    $work->status = '進行中';
                    break;
                case $work->status === 2:
                    $work->status = '完了';
                    break;
                default:
                    $work->status = '未知';
                    break;
            };
        }

        return view('works.index', compact('works'));
    }

    /**
     * 新規作成
     */
    public function create(Request $request)
    {
        $work = new Work();

        if ($request->isMethod('post')) {
            $work->name = $request->name;
            $work->description = $request->description;
            $work->client_id = $request->client_id;
            $work->related_task_id = $request->related_task_id;
            $work->start_date = $request->start_date;
            $work->end_date = $request->end_date;
            $work->payment_date = $request->payment_date;
            $work->price = $request->price;
            $work->status = $request->status;

            if ($work->save()) {
                return redirect('/works')->with([
                    'flash_message' => 'Successful',
                    'flash_status' => 'success',
                    'flash_icon' => 'check-circle-fill',
                ]);
            }
        }

        return view('works.form', compact('work'));
    }

    /**
     * 編集
     */
    public function edit(Request $request)
    {
        $work = Work::find($request->id);

        if ($request->isMethod('post')) {
            $work->name = $request->name;
            $work->description = $request->description;
            $work->client_id = $request->client_id;
            $work->related_task_id = $request->related_task_id;
            $work->start_date = $request->start_date;
            $work->end_date = $request->end_date;
            $work->payment_date = $request->payment_date;
            $work->price = $request->price;
            $work->status = $request->status;

            if ($work->save()) {
                return redirect('/works')->with([
                    'flash_message' => 'Successful',
                    'flash_status' => 'success',
                    'flash_icon' => 'check-circle-fill',
                ]);
            }
        }
        return view('works.form', compact('work'));
    }

    /**
     * プロジェクトの売り上げ分析
     * TODO: 検索パラメータを追加
     */
    public function analysis(Request $request)
    {
        $columns = [
            [
                'field' => 'payment_date',
                'name' => '支払日',
            ],
            [
                'field' => 'start_date',
                'name' => '開始日',
            ],
            [
                'field' => 'end_date',
                'name' => '終了日',
            ],
        ];
        $year = $request->input('year') ?? intval(date('Y'));
        $month = $request->input('month') ?? intval(date('m'));
        $search_column = $request->input('type') ?? $columns[0]['field'];

        $works = Work::whereYear($search_column, $year)->whereMonth($search_column, $month)->get();
        $total_price = Work::whereYear($search_column, $year)->whereMonth($search_column, $month)->sum('price');

        return view('works.analysis', compact('works', 'total_price', 'year', 'month', 'columns', 'search_column'));
    }
}
