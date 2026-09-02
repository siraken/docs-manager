<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\SpreadSheet\UseCase\AppendSpreadSheetRowUseCase;
use Illuminate\Http\JsonResponse;

/**
 * Google スプレッドシート連携の疎通確認。
 *
 * TODO: 移行前から「ダミーの 1 行を書き込む」だけの動作確認用エンドポイント。
 *       業務上の用途 (何をシートに出すのか) が決まっていないため、そのまま
 *       残してある。用途が決まったら専用のユースケースに置き換えること。
 */
final class SpreadSheetController extends Controller
{
    public function store(AppendSpreadSheetRowUseCase $appendRow): JsonResponse
    {
        $appendRow->execute(['Test', 12345, true]);

        return response()->json(['message' => 'Test data are stored successfully.']);
    }
}
