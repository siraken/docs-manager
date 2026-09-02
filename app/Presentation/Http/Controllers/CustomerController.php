<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\Customer\UseCase\CreateCustomerUseCase;
use App\Application\Customer\UseCase\GetCustomerUseCase;
use App\Application\Customer\UseCase\ListCustomersUseCase;
use App\Application\Customer\UseCase\UpdateCustomerUseCase;
use App\Presentation\Http\Requests\SaveCustomerRequest;
use App\Presentation\Http\Support\Flash;
use App\Presentation\Http\ViewModels\CustomerView;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class CustomerController extends Controller
{
    public function index(ListCustomersUseCase $listCustomers): View
    {
        return view('customers.index', [
            'customers' => CustomerView::collection($listCustomers->execute()),
        ]);
    }

    public function create(): View
    {
        return view('customers.form', [
            'customer' => CustomerView::empty(),
            'isNew' => true,
        ]);
    }

    public function store(SaveCustomerRequest $request, CreateCustomerUseCase $createCustomer): RedirectResponse
    {
        $createCustomer->execute($request->toInput());

        return redirect()->route('customers.index')->with(Flash::success('顧客を登録しました'));
    }

    public function edit(int $id, GetCustomerUseCase $getCustomer): View
    {
        return view('customers.form', [
            'customer' => CustomerView::fromEntity($getCustomer->execute($id)),
            'isNew' => false,
        ]);
    }

    /**
     * 顧客の更新。
     *
     * 移行前は edit() に POST 分岐が無く、保存ボタンを押しても何も起きなかった
     * (画面はフォームに戻るだけなので、成功したように見えていた)。
     */
    public function update(SaveCustomerRequest $request, int $id, UpdateCustomerUseCase $updateCustomer): RedirectResponse
    {
        $updateCustomer->execute($id, $request->toInput());

        return redirect()->route('customers.index')->with(Flash::success('顧客を更新しました'));
    }
}
