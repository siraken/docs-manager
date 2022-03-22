<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Access;

class LogController extends Controller
{
    /**
     * アクセスログ
     */
    public function access(Request $request)
    {
        $logs = Access::all();
        return view('logs/access', compact('logs'));
    }
}
