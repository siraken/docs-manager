<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\Freee\Exception\FreeeApiException;
use App\Application\Freee\Input\FreeeResource;
use App\Application\Freee\UseCase\ExchangeFreeeAuthorizationCodeUseCase;
use App\Application\Freee\UseCase\FetchFreeeResourceUseCase;
use App\Application\Freee\UseCase\RefreshFreeeTokenUseCase;
use Illuminate\Http\JsonResponse;

/**
 * freee 会計 API の薄い中継。
 *
 * 移行前はクラス名が小文字始まり (freeeController) で、5 つのリソース取得が
 * ほぼ同じ cURL のコピーだった。取得処理は FreeeResource で 1 本にまとめている。
 *
 * TODO: 取得したトークンをどこにも保存していない (画面に出すだけ)。
 *       アクセストークンの保管と自動更新の置き場所を決めること。
 */
final class FreeeController extends Controller
{
    public function getAccessTokenByAuthCode(string $code, ExchangeFreeeAuthorizationCodeUseCase $exchange): JsonResponse
    {
        return $this->respond(fn (): array => $exchange->execute($code));
    }

    public function getTokenByRefreshToken(string $refresh_token, RefreshFreeeTokenUseCase $refresh): JsonResponse
    {
        return $this->respond(fn (): array => $refresh->execute($refresh_token));
    }

    public function getCompanies(FetchFreeeResourceUseCase $fetch): JsonResponse
    {
        return $this->respond(fn (): array => $fetch->execute(FreeeResource::Companies));
    }

    public function getWalletables(FetchFreeeResourceUseCase $fetch): JsonResponse
    {
        return $this->respond(fn (): array => $fetch->execute(FreeeResource::Walletables));
    }

    public function getPartners(FetchFreeeResourceUseCase $fetch): JsonResponse
    {
        return $this->respond(fn (): array => $fetch->execute(FreeeResource::Partners));
    }

    public function getQuotations(FetchFreeeResourceUseCase $fetch): JsonResponse
    {
        return $this->respond(fn (): array => $fetch->execute(FreeeResource::Quotations));
    }

    public function getInvoices(FetchFreeeResourceUseCase $fetch): JsonResponse
    {
        return $this->respond(fn (): array => $fetch->execute(FreeeResource::Invoices));
    }

    /**
     * API 呼び出しの失敗を 502 として返す。
     * 移行前は cURL の戻り値をそのまま出していたため、失敗しても 200 だった。
     *
     * @param \Closure(): array<string, mixed> $call
     */
    private function respond(\Closure $call): JsonResponse
    {
        try {
            return response()->json($call());
        } catch (FreeeApiException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }
}
