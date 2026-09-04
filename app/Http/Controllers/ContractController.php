<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Contract\UseCase\CreateContractUseCase;
use App\Application\Contract\UseCase\DeleteContractUseCase;
use App\Application\Contract\UseCase\GetContractUseCase;
use App\Application\Contract\UseCase\ListContractsUseCase;
use App\Application\Contract\UseCase\UpdateContractUseCase;
use App\Application\Customer\UseCase\ListCustomersUseCase;
use App\Domain\Customer\Entity\Customer;
use App\Http\Requests\SaveContractRequest;
use App\Http\ViewModels\ContractView;
use App\Http\ViewModels\CustomerView;
use App\Support\Flash;
use App\Support\Lookup;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * 契約管理。in-house-timecard-app から移植した機能。
 *
 * 移植元は編集画面がコントローラの渡さない変数 ($name / $pid など) を参照し、
 * form の action も id 抜きで route('contracts.update') を組もうとしていたため、
 * 編集画面を開いた時点で 500 になっていた。
 */
final class ContractController extends Controller
{
    public function index(
        ListContractsUseCase $listContracts,
        ListCustomersUseCase $listCustomers,
    ): InertiaResponse {
        return Inertia::render('Contracts/Index', [
            'contracts' => ContractView::collection(
                $listContracts->execute(),
                $this->customerNames($listCustomers->execute()),
            ),
            'urls' => [
                'create' => route('contracts.create'),
            ],
        ]);
    }

    public function create(ListCustomersUseCase $listCustomers): InertiaResponse
    {
        return Inertia::render('Contracts/Form', [
            'contract' => null,
            'customers' => CustomerView::options($listCustomers->execute()),
            'urls' => [
                'submit' => route('contracts.create'),
                'back' => route('contracts.index'),
            ],
        ]);
    }

    public function store(SaveContractRequest $request, CreateContractUseCase $createContract): RedirectResponse
    {
        $createContract->execute($request->toInput());

        return redirect()->route('contracts.index')->with(Flash::success('契約を登録しました'));
    }

    public function edit(
        int $id,
        GetContractUseCase $getContract,
        ListCustomersUseCase $listCustomers,
    ): InertiaResponse {
        $customers = $listCustomers->execute();
        $contract = $getContract->execute($id);

        return Inertia::render('Contracts/Form', [
            'contract' => ContractView::fromEntity(
                $contract,
                $this->customerNames($customers)[$contract->customerId()] ?? '',
            ),
            'customers' => CustomerView::options($customers),
            'urls' => [
                'submit' => route('contracts.edit', ['id' => $id]),
                'back' => route('contracts.index'),
            ],
        ]);
    }

    public function update(
        SaveContractRequest $request,
        int $id,
        UpdateContractUseCase $updateContract,
    ): RedirectResponse {
        $updateContract->execute($id, $request->toInput());

        return redirect()->route('contracts.index')->with(Flash::success('契約を更新しました'));
    }

    public function destroy(int $id, DeleteContractUseCase $deleteContract): RedirectResponse
    {
        $deleteContract->execute($id);

        return redirect()->route('contracts.index')->with(Flash::success('契約を削除しました'));
    }

    /**
     * 顧客 ID => 顧客名。一覧で契約ごとに顧客を引くと N+1 になるため、
     * まとめて引いて対応表にする (OrderController と同じ形)。
     *
     * @param list<Customer> $customers
     * @return array<int, string>
     */
    private function customerNames(array $customers): array
    {
        return Lookup::byId($customers, static fn (Customer $c): string => $c->name());
    }
}
