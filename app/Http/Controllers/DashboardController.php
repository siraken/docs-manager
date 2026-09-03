<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

final class DashboardController extends Controller
{
    public function index(): InertiaResponse
    {
        // TODO: ダッシュボードは静的な入り口のまま。発注書の件数や今月の売上など、
        //       既存のユースケースから引ける値を出す余地がある。
        return Inertia::render('Dashboard', [
            'shortcuts' => [
                [
                    'label' => '出張申請',
                    'description' => '出張の申請書を作成する',
                    'href' => route('trips.index'),
                    'icon' => 'airplane',
                ],
                [
                    'label' => '出張旅費精算',
                    'description' => '出張にかかった費用を精算する',
                    'href' => route('expenses.index'),
                    'icon' => 'receipt',
                ],
                [
                    'label' => '発注書作成',
                    'description' => '取引先向けの発注書を作る',
                    'href' => route('orders.index'),
                    'icon' => 'file-earmark-text',
                ],
            ],
        ]);
    }
}
