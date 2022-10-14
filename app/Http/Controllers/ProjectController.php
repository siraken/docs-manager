<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Faker\Guesser\Name;

class ProjectController extends Controller
{
    /**
     * 一覧表示
     */
    public function index()
    {
        $projects = Project::all();
        foreach ($projects as $project) {
            switch ($project->status) {
                case $project->status === 0:
                    $project->status = '未着手';
                    break;
                case $project->status === 1:
                    $project->status = '進行中';
                    break;
                case $project->status === 2:
                    $project->status = '完了';
                    break;
                default:
                    $project->status = '未知';
                    break;
            };
        }

        return view('projects.index', compact('projects'));
    }

    /**
     * 新規作成
     */
    public function create(Request $request)
    {
        $project = new Project();

        if ($request->isMethod('post')) {
            $project->name = $request->name;
            $project->description = $request->description;
            $project->client_id = $request->client_id;
            $project->related_task_id = $request->related_task_id;
            $project->start_date = $request->start_date;
            $project->end_date = $request->end_date;
            $project->payment_date = $request->payment_date;
            $project->price = $request->price;
            $project->status = $request->status;

            if ($project->save()) {
                return redirect('/projects')->with([
                    'flash_message' => 'Successful',
                    'flash_status' => 'success',
                    'flash_icon' => 'check-circle-fill',
                ]);
            }
        }

        return view('projects.form', compact('project'));
    }

    /**
     * 編集
     */
    public function edit(Request $request)
    {
        $project = Project::find($request->id);

        if ($request->isMethod('post')) {
            $project->name = $request->name;
            $project->description = $request->description;
            $project->client_id = $request->client_id;
            $project->related_task_id = $request->related_task_id;
            $project->start_date = $request->start_date;
            $project->end_date = $request->end_date;
            $project->payment_date = $request->payment_date;
            $project->price = $request->price;
            $project->status = $request->status;

            if ($project->save()) {
                return redirect('/projects')->with([
                    'flash_message' => 'Successful',
                    'flash_status' => 'success',
                    'flash_icon' => 'check-circle-fill',
                ]);
            }
        }
        return view('projects.form', compact('project'));
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

        $projects = Project::whereYear($search_column, $year)->whereMonth($search_column, $month)->get();
        $total_price = Project::whereYear($search_column, $year)->whereMonth($search_column, $month)->sum('price');

        return view('projects.analysis', compact('projects', 'total_price', 'year', 'month', 'columns', 'search_column'));
    }
}
