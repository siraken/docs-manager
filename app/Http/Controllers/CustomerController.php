<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    /**
     * 一覧
     *
     *
     */
    public function index()
    {
        $customers = Customer::all();
        return view('customers.index', compact('customers'));
    }

    /**
     * 新規作成
     *
     *
     */
    public function create(Request $request)
    {
        $customer = new Customer();

        if ($request->isMethod('post')) {

            $request->is_company = $request->is_company ? 1 : 0;

            $customer->name = $request->name;
            $customer->is_company = $request->is_company;
            $customer->email = $request->email;
            $customer->phone = $request->phone;
            $customer->post_code = $request->post_code;
            $customer->address = $request->address;
            $customer->city = $request->city;
            $customer->state = $request->state;
            $customer->country = $request->country;
            $customer->note = $request->note;
            $customer->save();
            return redirect()->route('customers.index');
        }

        return view('customers.form', compact('customer'));
    }

    /**
     * 編集
     *
     *
     */
    public function edit($id)
    {
        $customer = Customer::find($id);
        return view('customers.form', compact('customer'));
    }
}
