<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function index(): View
    {
        // TODO: ダッシュボードは静的な入り口のまま。発注書の件数や今月の売上など、
        //       既存のユースケースから引ける値を出す余地がある。
        return view('dashboard');
    }
}
