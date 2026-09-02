<?php

declare(strict_types=1);

namespace App\Application\SpreadSheet\UseCase;

use App\Application\SpreadSheet\Port\SpreadSheetWriterInterface;

/**
 * スプレッドシートに 1 行追記する。
 *
 * TODO: 呼び出し元 (/sheet) は疎通確認用のダミー行を書くだけで、業務上の
 *       用途がまだ決まっていない。何を書き出すのかが決まったら、その
 *       ユースケース (例: 発注書の一覧をエクスポート) に置き換えること。
 */
final readonly class AppendSpreadSheetRowUseCase
{
    public function __construct(private SpreadSheetWriterInterface $writer)
    {
    }

    /** @param list<scalar|null> $values */
    public function execute(array $values): void
    {
        $this->writer->appendRow($values);
    }
}
