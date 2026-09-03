<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Setting\UseCase\GetCompanyProfileUseCase;
use App\Application\Setting\UseCase\UpdateCompanyProfileUseCase;
use App\Http\Requests\SaveCompanyProfileRequest;
use App\Support\Flash;
use App\Http\ViewModels\CompanyProfileView;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * 設定 (自社情報)。
 *
 * 移行前は中身が空のコントローラで、/settings は「設定項目はまだありません」と
 * 出すだけのクロージャだった。settings テーブルは最初からあったのに参照する
 * コードが無く、発注書 PDF の差出人欄は直書きのままだった。
 */
final class SettingController extends Controller
{
    public function index(GetCompanyProfileUseCase $getProfile): InertiaResponse
    {
        return Inertia::render('Settings', [
            'profile' => CompanyProfileView::fromEntity($getProfile->execute()),
            'urls' => ['submit' => route('settings.index')],
        ]);
    }

    public function update(SaveCompanyProfileRequest $request, UpdateCompanyProfileUseCase $updateProfile): RedirectResponse
    {
        $updateProfile->execute($request->toInput());

        return redirect()->route('settings.index')->with(Flash::success('自社情報を保存しました'));
    }
}
