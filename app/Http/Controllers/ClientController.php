<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::all();
        return view('client/index', compact('clients'));
    }

    /**
     * Create
     *
     */
    public function create(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $client = new Client();
            if ($client->fill($request->all())->save())
            {
                return redirect('/client')->with('flash_message', 'Successful');
            }
        }

        return view('client/create');
    }

    /**
     * Truncate
     *
     */
    public function truncate(Request $request)
    {
        if (Client::truncate())
        {
            return redirect('/client')->with('flash_message', 'Truncate Successful');
        }
    }
}
