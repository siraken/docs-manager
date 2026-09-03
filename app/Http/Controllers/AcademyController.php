<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Academy\UseCase\ListAcademyInquiriesUseCase;
use App\Application\Academy\UseCase\RegisterAcademyInquiryUseCase;
use App\Http\Requests\RegisterAcademyInquiryRequest;
use App\Http\ViewModels\AcademyInquiryView;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Lumo Academy の問い合わせ受付。
 *
 * register は認証不要の公開エンドポイント (外部サイトのフォームから叩かれる)。
 * index は管理側の一覧で、login ミドルウェアの内側にある。
 */
final class AcademyController extends Controller
{
    public function index(ListAcademyInquiriesUseCase $listInquiries): InertiaResponse
    {
        return Inertia::render('Academy/Index', [
            'inquiries' => AcademyInquiryView::collection($listInquiries->execute()),
        ]);
    }

    /**
     * 問い合わせの登録。
     *
     * 移行前は fill() を呼ぶだけで save() しておらず、送信された内容が
     * どこにも残っていなかった。
     */
    public function register(RegisterAcademyInquiryRequest $request, RegisterAcademyInquiryUseCase $register): JsonResponse
    {
        $inquiry = $register->execute($request->toInput());

        return response()->json([
            'status' => 'ok',
            'id' => $inquiry->id(),
        ], 201);
    }
}
