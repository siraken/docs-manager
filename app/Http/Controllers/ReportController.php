<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * 一覧画面表示
     *
     * @param Request $request
     * @param int $year
     * @param int $month
     */
    public function index(Request $request, $year = null, $month = null)
    {
        $q = $request->query('q');

        $query = Report::query();

        if ($q !== null) {
            $query->where('title', 'like', '%' . $q . '%');
        }

        if ($year !== null && $month !== null) {
            $query->whereYear('date', $year)->whereMonth('date', $month);
        }

        $reports = $query->orderBy('date', 'desc')->paginate(10);
        $totalWorkDays = $reports->sum('work_days');

        return view('reports.index', compact('reports', 'totalWorkDays', 'q'));
    }

    /**
     * 詳細画面表示
     *
     * @param int $id
     */
    public function show($id)
    {
        $report = Report::find($id);

        return view('reports.show', compact('report'));
    }

    /**
     * 登録画面表示
     *
     */
    public function create()
    {
        $users = User::all();
        $clients = Client::all();
        $projects = Project::all();

        return view('reports.create', compact('users', 'clients', 'projects'));
    }

    /**
     * 登録処理
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $report = new Report();
        $report->fill($request->all())->save();

        return redirect()->route('reports.index');
    }

    /**
     * 編集画面表示
     *
     * @param int $id
     */
    public function edit($id)
    {
        $report = Report::find($id);

        return view('reports.edit', compact('report'));
    }

    /**
     * 更新処理
     *
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request, $id)
    {
        $report = Report::find($id);
        $report->fill($request->all())->save();

        return redirect()->route('reports.index');
    }

    /**
     * 削除処理
     *
     * @param int $id
     */
    public function delete($id)
    {
        $report = Report::find($id);
        $report->delete();

        return redirect()->route('reports.index');
    }
}
