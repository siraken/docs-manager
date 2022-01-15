<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReceiveOrderController extends Controller
{
    //
    public function index()
    {
        $estimates = [];
        return view('order/index', compact('estimates'));
    }
}
