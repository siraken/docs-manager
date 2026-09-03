<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;

class ContractController extends Controller
{
    /**
     * 一覧画面表示
     *
     */
    public function index()
    {
        $contracts = Contract::all();
        return view('contracts.index', compact('contracts'));
    }

    /**
     * 詳細画面表示
     *
     * @param int $id
     */
    public function show($id)
    {
        $contract = Contract::find($id);
        return view('contracts.show', compact('contract'));
    }

    /**
     * 登録画面表示
     *
     */
    public function create()
    {
        return view('contracts.create');
    }

    /**
     * 登録処理
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $contract = new Contract();
        $contract->fill($request->all())->save();
        return redirect()->route('contracts.index');
    }

    /**
     * 編集画面表示
     *
     * @param int $id
     */
    public function edit($id)
    {
        $contract = Contract::find($id);
        return view('contracts.edit', compact('contract'));
    }

    /**
     * 更新処理
     *
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request)
    {
        $contract = Contract::find($request->id);
        $contract->fill($request->all())->save();
        return redirect()->route('contracts.index');
    }

    /**
     * 削除処理
     *
     * @param int $id
     */
    public function destroy($id)
    {
        $contract = Contract::find($id);
        $contract->delete();
        return redirect()->route('contracts.index');
    }
}
