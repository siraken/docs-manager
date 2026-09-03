<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Auth\Exception\AuthenticationFailedException;
use App\Application\Auth\Exception\NoUsersRegisteredException;
use App\Application\Auth\Port\LoginContext;
use App\Application\Auth\UseCase\LoginWithNfcUseCase;
use App\Application\Auth\UseCase\LoginWithPasswordUseCase;
use App\Application\Auth\UseCase\LoginWithWalletUseCase;
use App\Application\Auth\UseCase\LogoutUseCase;
use App\Http\Requests\LoginRequest;
use App\Support\Flash;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * 独自セッション認証の入り口。
 *
 * このアプリは Illuminate\Auth を使わない。セッションの組み立ては
 * Infrastructure 層の SessionAuthStore が担当し、ここは HTTP との変換だけ行う。
 */
final class LoginController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Auth/Login', [
            'urls' => [
                'submit' => route('loginAuth'),
                'nfc' => route('login-nfc'),
                'metamask' => route('login-metamask'),
            ],
        ]);
    }

    public function auth(LoginRequest $request, LoginWithPasswordUseCase $login): RedirectResponse
    {
        try {
            $user = $login->execute($request->email(), $request->password(), $this->context($request));
        } catch (NoUsersRegisteredException) {
            // 初期構築時。ユーザーが 1 人もいないので作成画面へ送る
            return redirect('/users/create');
        } catch (AuthenticationFailedException $e) {
            return redirect('/login')->with(Flash::error($e->getMessage()));
        }

        return redirect('/')->with(Flash::success('Logged in as ' . $user->name()));
    }

    public function authWithNfc(Request $request, LoginWithNfcUseCase $login): RedirectResponse
    {
        try {
            $login->execute($request->input('serialNumber'), $request->input('pin'));
        } catch (AuthenticationFailedException $e) {
            return redirect('/login')->with(Flash::error($e->getMessage()));
        }

        return redirect('/')->with(Flash::success('Logged in with NFC.'));
    }

    public function authWithMetamask(Request $request, LoginWithWalletUseCase $login): RedirectResponse
    {
        try {
            $login->execute($request->input('address'));
        } catch (AuthenticationFailedException $e) {
            return redirect('/login')->with(Flash::error($e->getMessage()));
        }

        return redirect('/')->with(Flash::success('Logged in with Metamask.'));
    }

    public function destroy(LogoutUseCase $logout): RedirectResponse
    {
        $logout->execute();

        return redirect('/login')->with(Flash::success('Logged out.'));
    }

    /** ログイン通知メールに載せるリクエスト情報 */
    private function context(Request $request): LoginContext
    {
        return new LoginContext(
            ipAddress: $request->ip(),
            userAgent: $request->header('User-Agent'),
            occurredAt: new \DateTimeImmutable(),
        );
    }
}
