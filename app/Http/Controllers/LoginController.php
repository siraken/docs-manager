<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Access;

class LoginController extends Controller
{
    /**
     * ログイン認証
     */
    public function auth(Request $request)
    {
        $access = new Access();

        // return response('This website is not working.', 500)
        //     ->header('Content-Type', 'text/plain');

        $access->timestamps = false;

        if (User::all()->count() === 0)
        {
            return redirect('/users/create');
        }

        $user = User::where('email', $request->email)->first();

        // ユーザーが存在しない場合
        if ($user === null)
        {
            // アクセスログの記録
            $access->user_id = null;
            $access->status = 'not found [' . $request->email . ']';
            $access->access_date = date('Y-m-d H:i:s');
            // TODO: IPアドレスを取得する
            // $request-> ip()
            $access->save();

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
                'user_id' => $user->id,
                'name'  => $user->name,
                'email' => $user->email
            ]);

            // アクセスログの記録
            $access->user_id = $user->id;
            $access->status = 'logged in';
            $access->access_date = date('Y-m-d H:i:s');
            $access->save();

            return redirect('/')->with([
                'flash_message' => 'Logged in as ' . $user->name,
                'flash_status' => 'success',
                'flash_icon' => 'check-circle-fill',
            ]);
        }
        else
        {
            // アクセスログの記録
            $access->user_id = $user->id;
            $access->status = 'wrong password [' . $request->email . ':' . $request->password . ']';
            $access->access_date = date('Y-m-d H:i:s');
            $access->save();

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
        // アクセスログの記録
        $access = new Access();
        $access->timestamps = false;
        $access->user_id = session('user_id');
        $access->status = 'logged out';
        $access->access_date = date('Y-m-d H:i:s');
        $access->save();

        // セッションを破棄
        session()->flush();

        return redirect('/login')->with([
            'flash_message' => 'Logged out.',
            'flash_status' => 'success',
            'flash_icon' => 'check-circle-fill',
        ]);
    }
}
