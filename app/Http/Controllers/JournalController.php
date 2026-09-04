<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Accounting\Input\JournalFilter;
use App\Application\Accounting\UseCase\BuildTrialBalanceUseCase;
use App\Application\Accounting\UseCase\CreateJournalEntryUseCase;
use App\Application\Accounting\UseCase\DeleteJournalEntryUseCase;
use App\Application\Accounting\UseCase\GetJournalEntryUseCase;
use App\Application\Accounting\UseCase\ListAccountsUseCase;
use App\Application\Accounting\UseCase\ListJournalEntriesUseCase;
use App\Application\Accounting\UseCase\UpdateJournalEntryUseCase;
use App\Domain\Accounting\Entity\Account;
use App\Http\Requests\SaveJournalEntryRequest;
use App\Http\ViewModels\AccountView;
use App\Http\ViewModels\JournalEntryView;
use App\Http\ViewModels\TrialBalanceRowView;
use App\Support\Flash;
use App\Support\Lookup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * 仕訳帳と残高試算表。
 *
 * in-house-timecard-app に CakePHP 時代のテンプレート
 * (resources/views/Posts/*.ctp) だけが残っていた機能を、参考にしつつ
 * 新規に作り直したもの。参考実装との違いは docs/history/timecard-port.md を参照。
 */
final class JournalController extends Controller
{
    public function index(
        Request $request,
        ListJournalEntriesUseCase $listEntries,
        ListAccountsUseCase $listAccounts,
    ): InertiaResponse {
        $filter = $this->filterFrom($request);
        $journal = $listEntries->execute($filter);
        $accounts = $listAccounts->execute();

        return Inertia::render('Journal/Index', [
            'entries' => JournalEntryView::collection($journal->entries, $this->accountNames($accounts)),
            'total' => $journal->total->amount,
            'totalLabel' => $journal->total->format(),
            'accounts' => AccountView::options($accounts),
            'filter' => [
                'year' => $filter->year,
                'month' => $filter->month,
                'keyword' => $filter->keyword,
                'accountId' => $filter->accountId,
            ],
            'years' => $this->years(),
            'urls' => [
                'self' => route('journal.index'),
                'create' => route('journal.create'),
                'trialBalance' => route('journal.trialBalance'),
                'accounts' => route('accounts.index'),
            ],
        ]);
    }

    public function create(ListAccountsUseCase $listAccounts): InertiaResponse
    {
        return Inertia::render('Journal/Form', [
            'entry' => null,
            // 日付の既定値はサーバーで決める (画面が new Date() を持つと
            // サーバーの時計とずれ、テストから固定できない)
            'defaults' => ['date' => date('Y-m-d')],
            'accounts' => AccountView::options($listAccounts->execute(activeOnly: true)),
            'urls' => [
                'submit' => route('journal.create'),
                'back' => route('journal.index'),
            ],
        ]);
    }

    public function store(SaveJournalEntryRequest $request, CreateJournalEntryUseCase $createEntry): RedirectResponse
    {
        $createEntry->execute($request->toInput());

        return redirect()->route('journal.index')->with(Flash::success('仕訳を登録しました'));
    }

    public function edit(
        int $id,
        GetJournalEntryUseCase $getEntry,
        ListAccountsUseCase $listAccounts,
    ): InertiaResponse {
        $entry = $getEntry->execute($id);

        // 編集では無効化済みの科目も選択肢に残す。既にその科目で記帳された
        // 仕訳を開いたときに、選択が外れて別の科目に化けるのを防ぐため
        $accounts = $listAccounts->execute();

        return Inertia::render('Journal/Form', [
            'entry' => JournalEntryView::fromEntity($entry, $this->accountNames($accounts)),
            'defaults' => null,
            'accounts' => AccountView::options($accounts),
            'urls' => [
                'submit' => route('journal.edit', ['id' => $id]),
                'back' => route('journal.index'),
            ],
        ]);
    }

    public function update(
        SaveJournalEntryRequest $request,
        int $id,
        UpdateJournalEntryUseCase $updateEntry,
    ): RedirectResponse {
        $updateEntry->execute($id, $request->toInput());

        return redirect()->route('journal.index')->with(Flash::success('仕訳を更新しました'));
    }

    public function destroy(int $id, DeleteJournalEntryUseCase $deleteEntry): RedirectResponse
    {
        $deleteEntry->execute($id);

        return redirect()->route('journal.index')->with(Flash::success('仕訳を削除しました'));
    }

    /**
     * 残高試算表。
     *
     * 科目での絞り込みは効かせない。1 科目だけを抜き出した表は貸借が
     * 一致せず、試算表として意味を成さないため。
     */
    public function trialBalance(Request $request, BuildTrialBalanceUseCase $build): InertiaResponse
    {
        $filter = $this->filterFrom($request);
        $balance = $build->execute($filter);

        return Inertia::render('Journal/TrialBalance', [
            'rows' => TrialBalanceRowView::collection($balance->rows),
            'totals' => [
                'debitTotal' => $balance->debitTotal->format(),
                'creditTotal' => $balance->creditTotal->format(),
                'debitBalance' => $balance->debitBalanceTotal->format(),
                'creditBalance' => $balance->creditBalanceTotal->format(),
                // 単一仕訳しか作れないので常に true になるはず。false が出たら不具合
                'isBalanced' => $balance->isBalanced(),
            ],
            'filter' => ['year' => $balance->year, 'month' => $balance->month],
            'years' => $this->years(),
            'urls' => [
                'self' => route('journal.trialBalance'),
                'back' => route('journal.index'),
            ],
        ]);
    }

    private function filterFrom(Request $request): JournalFilter
    {
        return JournalFilter::of(
            $request->input('year'),
            $request->input('month'),
            $request->input('q'),
            $request->input('account_id'),
        );
    }

    /**
     * 年の選択肢。
     *
     * @return list<int>
     */
    private function years(): array
    {
        return range(2020, (int) date('Y') + 1);
    }

    /**
     * 科目 ID => 表示名。一覧で仕訳ごとに科目を引くと N+1 になるため、
     * まとめて引いて対応表にする。
     *
     * @param list<Account> $accounts
     * @return array<int, string>
     */
    private function accountNames(array $accounts): array
    {
        return Lookup::byId($accounts, static fn (Account $a): string => $a->displayName());
    }
}
