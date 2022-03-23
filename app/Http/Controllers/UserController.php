<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    /**
     * ユーザー一覧
     */
    public function index()
    {
        $users = User::all();
        return view('users/index', compact('users'));
    }

    /**
     * Create
     *
     */
    public function create(Request $request)
    {
        $user = new User();

        if ($request->isMethod('POST'))
        {
            if ($user->fill([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)])->save())
            {
                return redirect('/users')->with([
                    'flash_message' => 'Successful',
                    'flash_status' => 'success',
                    'flash_icon' => 'check-circle-fill',
                ]);
            }
        }

        return view('users/form', compact('user'));
    }

    /**
     * Edit
     *
     */
    public function edit(Request $request, $id = null)
    {
        $user = User::find($id);

        if ($user === null) {
            abort(404, 'Not Found ;(');
        }

        if ($request->isMethod('POST'))
        {
            if ($user->fill([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)])->save())
            {
                return redirect('/users')->with([
                    'flash_message' => 'Successful',
                    'flash_status' => 'success',
                    'flash_icon' => 'check-circle-fill',
                ]);
            }
        }
        return view('users/form', compact('user'));
    }
}
