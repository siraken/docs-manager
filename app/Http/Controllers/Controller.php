<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * コントローラの基底。
 *
 * この層の役割は「HTTP をユースケースの入出力に変換すること」だけで、
 * 業務ロジックは持たない。DispatchesJobs はどのコントローラでも使って
 * いなかったため外してある。
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;
}
