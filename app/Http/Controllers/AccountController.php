<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Accounting\UseCase\CreateAccountUseCase;
use App\Application\Accounting\UseCase\DeleteAccountUseCase;
use App\Application\Accounting\UseCase\GetAccountUseCase;
use App\Application\Accounting\UseCase\ListAccountsUseCase;
use App\Application\Accounting\UseCase\UpdateAccountUseCase;
use App\Domain\Accounting\ValueObject\AccountType;
use App\Http\Requests\SaveAccountRequest;
use App\Http\ViewModels\AccountView;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * 勘定科目マスタ。仕訳帳が科目を選ぶ元になる。
 *
 * 参考にした移植元 (in-house-timecard-app の CakePHP 時代の仕訳帳) は
 * 科目を自由入力の文字列で持っていたため、マスタそのものが存在しなかった。
 */
final class AccountController extends Controller
{
    public function index(ListAccountsUseCase $listAccounts): InertiaResponse
    {
        return Inertia::render('Accounts/Index', [
            'accounts' => AccountView::collection($listAccounts->execute()),
            'urls' => [
                'create' => route('accounts.create'),
                'journal' => route('journal.index'),
            ],
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Accounts/Form', [
            'account' => null,
            'types' => $this->typeOptions(),
            'urls' => [
                'submit' => route('accounts.create'),
                'back' => route('accounts.index'),
            ],
        ]);
    }

    public function store(SaveAccountRequest $request, CreateAccountUseCase $createAccount): RedirectResponse
    {
        $createAccount->execute($request->toInput());

        return redirect()->route('accounts.index')->with(Flash::success('勘定科目を登録しました'));
    }

    public function edit(int $id, GetAccountUseCase $getAccount): InertiaResponse
    {
        return Inertia::render('Accounts/Form', [
            'account' => AccountView::fromEntity($getAccount->execute($id)),
            'types' => $this->typeOptions(),
            'urls' => [
                'submit' => route('accounts.edit', ['id' => $id]),
                'back' => route('accounts.index'),
            ],
        ]);
    }

    public function update(SaveAccountRequest $request, int $id, UpdateAccountUseCase $updateAccount): RedirectResponse
    {
        $updateAccount->execute($id, $request->toInput());

        return redirect()->route('accounts.index')->with(Flash::success('勘定科目を更新しました'));
    }

    /**
     * 削除。仕訳から参照されている科目は AccountInUseException になり、
     * bootstrap/app.php が元の画面へ戻してメッセージを出す。
     */
    public function destroy(int $id, DeleteAccountUseCase $deleteAccount): RedirectResponse
    {
        $deleteAccount->execute($id);

        return redirect()->route('accounts.index')->with(Flash::success('勘定科目を削除しました'));
    }

    /**
     * 区分の選択肢。ドメインの AccountType が唯一の定義。
     *
     * @return list<array{value: string, label: string}>
     */
    private function typeOptions(): array
    {
        return array_map(
            static fn (string $value, string $label): array => ['value' => $value, 'label' => $label],
            array_keys(AccountType::options()),
            array_values(AccountType::options()),
        );
    }
}
