<?php

declare(strict_types=1);

namespace App\Infrastructure\Freee;

use App\Application\Freee\Exception\FreeeApiException;
use App\Application\Freee\Input\FreeeResource;
use App\Application\Freee\Port\FreeeApiClientInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * freee 会計 API のクライアント。
 *
 * 移行前は cURL を直接叩き、レスポンスの本文を検証せずに文字列のまま返していた
 * (通信エラーは false、API エラーはエラー JSON がそのまま画面に出ていた)。
 * ここでは Laravel の HTTP クライアントを使い、失敗を例外にして返す。
 *
 * 認証情報は config/services.php 経由で読む。移行前は env() 直読みだったため、
 * config:cache 済みの環境では null になっていた。
 */
final class CurlFreeeApiClient implements FreeeApiClientInterface
{
    private const TOKEN_ENDPOINT = 'https://accounts.secure.freee.co.jp/public_api/token';
    private const API_BASE = 'https://api.freee.co.jp/api/1';

    /** OAuth の redirect_uri。ブラウザを介さない (out-of-band) 方式 */
    private const REDIRECT_URI = 'urn:ietf:wg:oauth:2.0:oob';

    private const TIMEOUT_SECONDS = 15;

    /** @return array<string, mixed> */
    public function exchangeAuthorizationCode(string $code): array
    {
        return $this->requestToken([
            'grant_type' => 'authorization_code',
            'code' => $code,
        ]);
    }

    /** @return array<string, mixed> */
    public function refreshAccessToken(?string $refreshToken): array
    {
        // ルートパラメータで値を渡さなかったときに文字列の "null" が届くため、
        // それも未指定とみなして設定値にフォールバックする (移行前と同じ挙動)
        if ($refreshToken === null || $refreshToken === '' || $refreshToken === 'null') {
            $refreshToken = (string) config('services.freee.refresh_token');
        }

        return $this->requestToken([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);
    }

    /** @return array<string, mixed> */
    public function fetch(FreeeResource $resource): array
    {
        $query = $resource->requiresCompanyId()
            ? ['company_id' => config('services.freee.company_id')]
            : [];

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->withToken((string) config('services.freee.token'))
                ->acceptJson()
                ->get(self::API_BASE . '/' . $resource->value, $query);
        } catch (ConnectionException $e) {
            throw new FreeeApiException('freee API に接続できませんでした。', previous: $e);
        }

        if ($response->failed()) {
            throw new FreeeApiException(sprintf('freee API がエラーを返しました (HTTP %d)。', $response->status()));
        }

        return (array) $response->json();
    }

    /**
     * @param array<string, string> $payload
     * @return array<string, mixed>
     */
    private function requestToken(array $payload): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->asForm()
                ->post(self::TOKEN_ENDPOINT, [
                    ...$payload,
                    'client_id' => (string) config('services.freee.client_id'),
                    'client_secret' => (string) config('services.freee.client_secret'),
                    'redirect_uri' => self::REDIRECT_URI,
                ]);
        } catch (ConnectionException $e) {
            throw new FreeeApiException('freee の認証エンドポイントに接続できませんでした。', previous: $e);
        }

        if ($response->failed()) {
            throw new FreeeApiException(sprintf('freee のトークン取得に失敗しました (HTTP %d)。', $response->status()));
        }

        return (array) $response->json();
    }
}
