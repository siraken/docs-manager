<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * ログイン認証
     */
    public function auth(Request $request)
    {
        if (User::all()->count() === 0)
        {
            return redirect('/users/create');
        }

        $user = User::where('email', $request->email)->first();

        // ユーザーが存在しない場合
        if ($user === null)
        {
            return redirect('/login')->with([
                'flash_message' => 'The user does not exist.',
                'flash_status' => 'danger',
                'flash_icon' => 'times-circle',
            ]);
        }

        // パスワードの一致確認
        if (Hash::check($request->password, $user->password))
        {
            // セッション
            session([
                'name'  => $user->name,
                'email' => $user->email
            ]);

            return redirect('/')->with([
                'flash_message' => 'Logged in as ' . $user->name,
                'flash_status' => 'success',
                'flash_icon' => 'check-circle-fill',
            ]);
        }
        else
        {
            return redirect('/login')->with([
                'flash_message' => 'Failed to login.',
                'flash_status' => 'danger',
                'flash_icon' => 'times-circle',
            ]);
        }
    }

    /**
     * ログアウト
     */
    public function destroy()
    {
        // セッションを破棄
        session()->flush();

        return redirect('/login')->with([
            'flash_message' => 'Logged out.',
            'flash_status' => 'success',
            'flash_icon' => 'check-circle-fill',
        ]);
    }
}
