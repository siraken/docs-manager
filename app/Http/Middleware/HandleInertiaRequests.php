<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * Inertia のリクエストに共通で載せるデータ。
 *
 * Blade でいう layouts/default.blade.php が session() を直接読んでいた部分
 * (ログインユーザー、ナビの現在位置、フラッシュメッセージ) が、ここを通って
 * Svelte 側の props になる。
 *
 * ルート名 → URL の解決もここで行う。Ziggy のようなルートヘルパを入れると
 * 依存が増えるので、画面から参照する URL はサーバー側で組んで渡す方針。
 * 一覧の各行のリンク (編集・PDF など) は ViewModel が持つ。
 */
final class HandleInertiaRequests extends Middleware
{
    /**
     * Inertia が描画に使うルートテンプレート。
     * Blade のままの画面が使う layouts/default とは別物。
     */
    protected $rootView = 'app';

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            // parent::share() はバリデーションエラー ($errors 相当) を載せる
            ...parent::share($request),

            'auth' => $this->auth(),
            'flash' => $this->flash($request),
            'nav' => $this->navigation($request),
            'menu' => $this->menu(),
        ];
    }

    /**
     * ログイン中のユーザー。このアプリは Illuminate\Auth を使わないので
     * session() から直接組む。
     *
     * @return array<string, string|null>|null
     */
    private function auth(): ?array
    {
        $name = session('name');

        if ($name === null) {
            return null;
        }

        $email = (string) session('email');

        return [
            'name' => (string) $name,
            'email' => $email,
            // 移行前の Blade は md5(session('email')) を直接書いていた。
            // メールが未設定だと md5(null) で非推奨警告が出るため文字列に寄せる。
            'avatarUrl' => 'https://www.gravatar.com/avatar/' . md5($email) . '?s=56&d=mp',
        ];
    }

    /**
     * フラッシュメッセージ。App\Support\Flash が入れた 3 キーをまとめる。
     *
     * @return array<string, string>|null
     */
    private function flash(Request $request): ?array
    {
        $message = $request->session()->get('flash_message');

        if ($message === null) {
            return null;
        }

        return [
            'message' => (string) $message,
            'status' => (string) $request->session()->get('flash_status', 'success'),
            'icon' => (string) $request->session()->get('flash_icon', 'check-circle-fill'),
        ];
    }

    /**
     * ヘッダーのナビ。現在位置の判定もサーバー側で済ませる
     * (Blade では request()->route()->named() を使っていた)。
     *
     * @return list<array{label: string, href: string, active: bool}>
     */
    private function navigation(Request $request): array
    {
        $items = [
            ['label' => '出張申請', 'route' => 'trips.index', 'pattern' => 'trips.*'],
            ['label' => '出張旅費精算', 'route' => 'expenses.index', 'pattern' => 'expenses.*'],
            ['label' => '発注書', 'route' => 'orders.index', 'pattern' => 'orders.*'],
            ['label' => '案件管理', 'route' => 'projects.index', 'pattern' => 'projects.*'],
        ];

        return array_map(static fn (array $item): array => [
            'label' => $item['label'],
            'href' => route($item['route']),
            'active' => $request->route()?->named($item['pattern']) ?? false,
        ], $items);
    }

    /**
     * ユーザーメニューの中身。
     *
     * @return array<string, string>
     */
    private function menu(): array
    {
        return [
            'home' => route('dashboard.index'),
            'files' => route('files.index'),
            'users' => route('users.index'),
            'customers' => route('customers.index'),
            'settings' => route('settings.index'),
            'logout' => route('logout'),
        ];
    }
}
