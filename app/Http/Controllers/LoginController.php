<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    /**
     * ログイン画面
     */
    public function index()
    {
        return view('login');
    }

    /**
     * ログイン認証
     */
    public function auth(Request $request)
    {

        if (User::all()->count() === 0) {
            return redirect('/users/create');
        }

        $user = User::where('email', $request->email)->first();

        // ユーザーが存在しない場合
        if ($user === null) {
            return redirect('/login')->with([
                'flash_message' => 'The user does not exist.',
                'flash_status' => 'danger',
                'flash_icon' => 'times-circle',
            ]);
        }

        // パスワードの一致確認
        if (Hash::check($request->password, $user->password)) {
            // セッション
            session([
                'user_id' => $user->id,
                'name'  => $user->name,
                'email' => $user->email
            ]);

            // Send email to user
            $this->send_email($request, $user);

            return redirect('/')->with([
                'flash_message' => 'Logged in as ' . $user->name,
                'flash_status' => 'success',
                'flash_icon' => 'check-circle-fill',
            ]);
        } else {

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
    public function destroy(Request $request)
    {
        // セッションを破棄
        session()->flush();

        return redirect('/login')->with([
            'flash_message' => 'Logged out.',
            'flash_status' => 'success',
            'flash_icon' => 'check-circle-fill',
        ]);
    }

    private function send_email($request, $user)
    {
        if (env('APP_ENV') === 'production') {
            Mail::send(
                [
                    'text' => 'emails.login'
                ],
                [
                    'datetime' => date('Y-m-d H:i:s'),
                    'name' => $user->name,
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                ],
                function ($message) use ($user) {
                    $message
                        ->from('system@novalumo.llc', 'Novalumo Docs Manager')
                        ->to($user->email, $user->name)
                        ->subject('ログイン通知');
                }
            );
        }
    }

    public function auth_with_nfc(Request $request)
    {
        // Get info
        $serial = $request->serialNumber;
        $pin = $request->pin;

        // Find user
        $user = User::where('nfc_serial_number', $serial)->where('nfc_pin', $pin)->first();

        if ($user !== null) {

            // JSON
            // return response()->json([
            //     'status' => 'success',
            //     'user' => [
            //         'id' => $user->id,
            //         'name' => $user->name,
            //         'email' => $user->email,
            //     ],
            // ]);

            // セッション
            session([
                'user_id' => $user->id,
                'name'  => $user->name,
                'email' => $user->email
            ]);

            return redirect('/')->with([
                'flash_message' => 'Logged in with NFC.',
                'flash_status' => 'success',
                'flash_icon' => 'check-circle-fill',
            ]);
        } else {
            return redirect('/login')->with([
                'flash_message' => 'Failed to login.',
                'flash_status' => 'danger',
                'flash_icon' => 'times-circle',
            ]);
        }
    }

    public function auth_with_metamask(Request $request)
    {
        // Get info
        // TODO: More secure way to get info
        // * Currently, this method can be used if one knows the address of the user through API.
        $address = $request->address;

        // Find user
        $user = User::where('wallet_address', $address)->first();

        if ($user !== null) {

            // JSON
            // return response()->json([
            //     'status' => 'success',
            //     'user' => [
            //         'id' => $user->id,
            //         'name' => $user->name,
            //         'email' => $user->email,
            //     ],
            // ]);

            // セッション
            session([
                'user_id' => $user->id,
                'name'  => $user->name,
                'email' => $user->email
            ]);

            return redirect('/')->with([
                'flash_message' => 'Logged in with Metamask.',
                'flash_status' => 'success',
                'flash_icon' => 'check-circle-fill',
            ]);
        } else {
            return redirect('/login')->with([
                'flash_message' => 'Failed to login.',
                'flash_status' => 'danger',
                'flash_icon' => 'times-circle',
            ]);
        }
    }
}
