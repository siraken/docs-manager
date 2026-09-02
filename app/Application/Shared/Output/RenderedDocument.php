<?php

declare(strict_types=1);

namespace App\Application\Shared\Output;

/**
 * 生成済みのダウンロード用ファイル。
 * コントローラがこれを HTTP レスポンスに載せる。
 */
final readonly class RenderedDocument
{
    public function __construct(
        public string $fileName,
        public string $contentType,
        public string $contents,
    ) {
    }
}
