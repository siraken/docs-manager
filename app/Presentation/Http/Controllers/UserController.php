<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\User\UseCase\ConfirmTwoFactorSetupUseCase;
use App\Application\User\UseCase\CreateUserUseCase;
use App\Application\User\UseCase\DeleteUserUseCase;
use App\Application\User\UseCase\GetUserUseCase;
use App\Application\User\UseCase\ListUsersUseCase;
use App\Application\User\UseCase\RegisterNfcCredentialUseCase;
use App\Application\User\UseCase\StartTwoFactorSetupUseCase;
use App\Application\User\UseCase\UpdateUserUseCase;
use App\Presentation\Http\Requests\ConfirmTwoFactorRequest;
use App\Presentation\Http\Requests\RegisterNfcRequest;
use App\Presentation\Http\Requests\StoreUserRequest;
use App\Presentation\Http\Requests\UpdateUserRequest;
use App\Presentation\Http\Support\Flash;
use App\Presentation\Http\ViewModels\UserView;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class UserController extends Controller
{
    public function index(ListUsersUseCase $listUsers): View
    {
        return view('users.index', [
            'users' => UserView::collection($listUsers->execute()),
        ]);
    }

    /**
     * 新規登録フォーム。
     *
     * 移行前はここで未保存の User を渡していたため、ビューの 2FA リンクが
     * route('users.2fa', ['id' => null]) を組もうとして 500 になっていた。
     * ビュー側は $user->id が null のときリンクを出さないようにしてある。
     */
    public function create(): View
    {
        return view('users.form', [
            'user' => UserView::empty(),
            'isNew' => true,
        ]);
    }

    public function store(StoreUserRequest $request, CreateUserUseCase $createUser): RedirectResponse
    {
        $createUser->execute($request->toInput());

        return redirect()->route('users.index')->with(Flash::success('ユーザーを登録しました'));
    }

    public function edit(int $id, GetUserUseCase $getUser): View
    {
        return view('users.form', [
            'user' => UserView::fromEntity($getUser->execute($id)),
            'isNew' => false,
        ]);
    }

    public function update(UpdateUserRequest $request, int $id, UpdateUserUseCase $updateUser): RedirectResponse
    {
        $updateUser->execute($id, $request->toInput());

        return redirect()->route('users.index')->with(Flash::success('ユーザーを更新しました'));
    }

    /**
     * ユーザーの削除。
     *
     * 一覧に削除ボタンはあったが、押すと未定義の JS 関数を呼ぶだけで
     * サーバー側の受け口が無かった。
     */
    public function destroy(int $id, DeleteUserUseCase $deleteUser): RedirectResponse
    {
        $deleteUser->execute($id);

        return redirect()->route('users.index')->with(Flash::success('ユーザーを削除しました'));
    }

    /** NFC カードの登録 (Scan した値をそのまま保存する) */
    public function registerNfc(RegisterNfcRequest $request, int $id, RegisterNfcCredentialUseCase $registerNfc): RedirectResponse
    {
        $registerNfc->execute($id, $request->toInput());

        return redirect()->route('users.index')->with(Flash::success('NFC カードを登録しました'));
    }

    /** 二段階認証の設定画面 (シークレットを発行して QR 用の URI を出す) */
    public function twoFactor(int $id, StartTwoFactorSetupUseCase $startSetup, GetUserUseCase $getUser): View
    {
        $setup = $startSetup->execute($id);

        return view('users.2fa', [
            'user' => UserView::fromEntity($getUser->execute($id)),
            'setup' => $setup,
        ]);
    }

    /** 認証アプリのコードを検証して二段階認証を有効にする */
    public function confirmTwoFactor(
        ConfirmTwoFactorRequest $request,
        int $id,
        ConfirmTwoFactorSetupUseCase $confirmSetup,
    ): RedirectResponse {
        $confirmSetup->execute($id, $request->code());

        return redirect()->route('users.index')->with(Flash::success('二段階認証を有効にしました'));
    }
}
