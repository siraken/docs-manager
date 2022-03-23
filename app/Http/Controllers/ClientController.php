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
        $client = new Client();

        if ($request->isMethod('POST'))
        {
            if ($client->fill($request->all())->save())
            {
                return redirect('/clients')->with('flash_message', 'Successful');
            }
        }

        return view('clients/form', compact('client'));
    }

    /**
     * Edit
     *
     */
    public function edit(Request $request, $id = null)
    {
        $client = Client::find($id);

        if ($request->isMethod('POST'))
        {
            if ($client->fill($request->all())->save())
            {
                return redirect('/clients')->with('flash_message', 'Successful');
            }
        }

        return view('clients/form', compact('client'));
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
