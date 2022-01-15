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
        return view('master/item/index', compact('items'));
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
                return redirect('/item')->with('flash_message', 'Successful');
            }
        }

        return view('master/item/create');
    }

    /**
     * Edit
     *
     */
    public function edit()
    {
        return view('master/item/edit');
    }
}
