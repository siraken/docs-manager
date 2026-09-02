<?php

declare(strict_types=1);

namespace App\Support;

/**
 * フラッシュメッセージの組み立て。
 *
 * layouts/default.blade.php の x-flash が flash_message / flash_status /
 * flash_icon の 3 つをまとめて読む。移行前はこの 3 つをリダイレクトのたびに
 * 手で書いていたため、色とアイコンの取り違えが起きやすかった。
 */
final class Flash
{
    /** @return array<string, string> */
    public static function success(string $message = '保存しました'): array
    {
        return self::make($message, 'success', 'check-circle-fill');
    }

    /** @return array<string, string> */
    public static function error(string $message = '処理に失敗しました'): array
    {
        return self::make($message, 'danger', 'x-circle-fill');
    }

    /** @return array<string, string> */
    public static function warning(string $message): array
    {
        return self::make($message, 'warning', 'exclamation-triangle-fill');
    }

    /** @return array<string, string> */
    private static function make(string $message, string $status, string $icon): array
    {
        return [
            'flash_message' => $message,
            'flash_status' => $status,
            'flash_icon' => $icon,
        ];
    }
}
