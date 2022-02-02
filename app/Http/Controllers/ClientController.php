<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::all();
        return view('clients/index', compact('clients'));
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
                return redirect('/clients')->with('flash_message', 'Successful');
            }
        }

        return view('clients/create');
    }

    /**
     * Truncate
     *
     */
    public function truncate(Request $request)
    {
        if (Client::truncate())
        {
            return redirect('/clients')->with('flash_message', 'Truncate Successful');
        }
    }
}
