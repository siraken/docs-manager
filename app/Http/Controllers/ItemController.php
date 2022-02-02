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
        if ($request->isMethod('POST'))
        {
            $item = new Item();
            if ($item->fill($request->all())->save())
            {
                return redirect('/items')->with('flash_message', 'Successful');
            }
        }

        return view('items/create');
    }

    /**
     * Edit
     *
     */
    public function edit(Request $request, $id = null)
    {
        $item = Item::find($id);

        if ($request->isMethod('POST'))
        {
            if ($item->fill($request->all())->save())
            {
                return redirect('/items')->with('flash_message', 'Successful');
            }
        }
        return view('items/edit', compact('item'));
    }
}
