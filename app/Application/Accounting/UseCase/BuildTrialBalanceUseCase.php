<?php

declare(strict_types=1);

namespace App\Application\Accounting\UseCase;

use App\Application\Accounting\Input\JournalFilter;
use App\Application\Accounting\Output\TrialBalance;
use App\Domain\Accounting\Repository\AccountRepositoryInterface;
use App\Domain\Accounting\Repository\JournalEntryRepositoryInterface;
use App\Domain\Accounting\Service\TrialBalanceBuilder;

/**
 * 残高試算表を組み立てる。
 *
 * 集計そのものはドメインサービス (TrialBalanceBuilder) が持ち、ここは
 * 「どの仕訳と科目を渡すか」だけを決める。
 */
final readonly class BuildTrialBalanceUseCase
{
    public function __construct(
        private AccountRepositoryInterface $accounts,
        private JournalEntryRepositoryInterface $entries,
        private TrialBalanceBuilder $builder,
    ) {
    }

    public function execute(JournalFilter $filter): TrialBalance
    {
        // 試算表は全科目が対象。科目での絞り込みは効かせない
        // (1 科目だけの試算表は貸借が一致せず、表として意味を成さない)
        $entries = $this->entries->search($filter->year, $filter->month, null, null);

        return TrialBalance::of(
            $this->builder->build($this->accounts->listAll(), $entries),
            $filter->year,
            $filter->month,
        );
    }
}
