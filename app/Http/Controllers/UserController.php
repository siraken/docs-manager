<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function postSignIn(Request $request)
    {
        $this->validate($request, [
            'email' => 'email|required',
            'password' => 'required|min:4'
        ]);

        if (Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')]))
        {
            return redirect()->route('orders.index')->with([
                'flash_message' => 'ログインに成功しました',
                'flash_status' => 'success',
                'flash_icon' => 'check-circle-fill',
            ]);
        }
        return redirect()->back()->with([
            'flash_message' => 'ログインに失敗しました',
            'flash_status' => 'danger',
            'flash_icon' => 'x-circle-fill',
        ]);
    }
}
