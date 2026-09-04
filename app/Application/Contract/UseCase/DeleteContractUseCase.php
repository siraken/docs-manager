<?php

declare(strict_types=1);

namespace App\Application\Contract\UseCase;

use App\Domain\Contract\Repository\ContractRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * 契約を削除する。
 *
 * 移植前は編集画面に削除ボタンがあるだけで type="button" のまま何も起きず、
 * サーバー側の受け口も無かった (ルートの destroy は Contract::delete を
 * 呼ぶが、そこへ至る導線がどこにも無かった)。
 */
final readonly class DeleteContractUseCase
{
    public function __construct(private ContractRepositoryInterface $contracts)
    {
    }

    public function execute(int $id): void
    {
        if ($this->contracts->findById($id) === null) {
            throw EntityNotFoundException::of('契約', $id);
        }

        $this->contracts->delete($id);
    }
}
