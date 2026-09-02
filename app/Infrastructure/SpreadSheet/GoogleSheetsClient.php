<?php

declare(strict_types=1);

namespace App\Infrastructure\SpreadSheet;

use App\Application\SpreadSheet\Port\SpreadSheetWriterInterface;

/**
 * Google スプレッドシートへの追記。
 *
 * 移行前は App\Models\SpreadSheet という Eloquent モデル (実体は static
 * ユーティリティ) で、書き込む値も呼び出し側のダミー配列に固定されていた。
 * ここではポートの実装として、渡された 1 行をシート末尾に追記する。
 *
 * 認証情報は resources/json/credentials.json (gitignore 済み)。
 * シート ID は config/services.php 経由で読む。
 */
final class GoogleSheetsClient implements SpreadSheetWriterInterface
{
    private const CREDENTIALS = 'json/credentials.json';

    /** @param list<scalar|null> $values */
    public function appendRow(array $values): void
    {
        $sheets = $this->service();
        $spreadsheetId = (string) config('services.google_sheets.spreadsheet_id');

        if ($spreadsheetId === '') {
            throw new \RuntimeException('スプレッドシートの ID が設定されていません (GOOGLE_SPREADSHEET_ID)。');
        }

        $range = new \Google_Service_Sheets_ValueRange();
        // append に渡すのは「行の配列」。移行前は ['values' => $row] という
        // 連想配列を渡していたため、意図した並びで書き込めていなかった。
        $range->setValues([$values]);

        $sheets->spreadsheets_values->append(
            $spreadsheetId,
            'A1',
            $range,
            ['valueInputOption' => 'USER_ENTERED'],
        );
    }

    private function service(): \Google_Service_Sheets
    {
        $credentialsPath = resource_path(self::CREDENTIALS);

        if (!is_file($credentialsPath)) {
            throw new \RuntimeException(
                sprintf('Google API の認証情報が見つかりません: %s (credentials.example.json を参照)', $credentialsPath),
            );
        }

        $client = new \Google_Client();
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $client->setAuthConfig($credentialsPath);

        return new \Google_Service_Sheets($client);
    }
}
