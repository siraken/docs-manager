<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Customer\UseCase\CreateCustomerUseCase;
use App\Application\Customer\UseCase\GetCustomerUseCase;
use App\Application\Customer\UseCase\ListCustomersUseCase;
use App\Application\Customer\UseCase\UpdateCustomerUseCase;
use App\Http\Requests\SaveCustomerRequest;
use App\Support\Flash;
use App\Http\ViewModels\CustomerView;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

final class CustomerController extends Controller
{
    public function index(ListCustomersUseCase $listCustomers): InertiaResponse
    {
        return Inertia::render('Customers/Index', [
            'customers' => CustomerView::collection($listCustomers->execute()),
            'urls' => ['create' => route('customers.create')],
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Customers/Form', [
            'customer' => null,
            'urls' => [
                'submit' => route('customers.create'),
                'back' => route('customers.index'),
            ],
        ]);
    }

    public function store(SaveCustomerRequest $request, CreateCustomerUseCase $createCustomer): RedirectResponse
    {
        $createCustomer->execute($request->toInput());

        return redirect()->route('customers.index')->with(Flash::success('顧客を登録しました'));
    }

    public function edit(int $id, GetCustomerUseCase $getCustomer): InertiaResponse
    {
        return Inertia::render('Customers/Form', [
            'customer' => CustomerView::fromEntity($getCustomer->execute($id)),
            'urls' => [
                'submit' => route('customers.edit', ['id' => $id]),
                'back' => route('customers.index'),
            ],
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
