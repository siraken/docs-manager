<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class ItemController extends Controller
{
    /**
     * Index
     *
     */
    public function index()
    {
        $items = Item::all();
        return view('items/index', compact('items'));
    }

    /**
     * Create
     *
     */
    public function create(Request $request)
    {
        $item = new Item();

        if ($request->isMethod('POST'))
        {
            if ($item->fill($request->all())->save())
            {
                return redirect('/items')->with([
                    'flash_message' => 'Successful',
                    'flash_status' => 'success',
                    'flash_icon' => 'check-circle-fill',
                ]);
            }
        }

        return view('items/form', compact('item'));
    }

    /**
     * Edit
     *
     */
    public function edit(Request $request, $id = null)
    {
        $item = Item::find($id);
        if ($item === null) {
            abort(404, 'Not Found ;(');
        }

        if ($request->isMethod('POST'))
        {
            if ($item->fill($request->all())->save())
            {
                return redirect('/items')->with([
                    'flash_message' => 'Successful',
                    'flash_status' => 'success',
                    'flash_icon' => 'check-circle-fill',
                ]);
            }
        }
        return view('items/form', compact('item'));
    }
}
