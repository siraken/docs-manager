<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Access;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $logs = Access::orderBy('id', 'desc')->take(5)->get();
        foreach ($logs as $log) {
            $user = User::find($log->user_id);
            $log->user_id = $user['name'] ?? 'Unknown';
        }
        return view('dashboard', compact('logs'));
    }
}
