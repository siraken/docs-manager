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
            return redirect()->route('order.index');
        }
        return redirect()->back();
    }
}
