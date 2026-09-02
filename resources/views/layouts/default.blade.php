<!DOCTYPE html>
<html lang="ja" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex" />
    <meta name="googlebot" content="nofollow" />
    <title>管理ツール</title>
    @vite(['resources/css/app.css', 'resources/ts/app.ts'])
</head>

<body class="flex min-h-full flex-col">
    <x-flash />

    @php
        $navigation = [
            ['label' => '出張申請', 'route' => 'trips.index', 'pattern' => 'trips.*'],
            ['label' => '出張旅費精算', 'route' => 'expenses.index', 'pattern' => 'expenses.*'],
            ['label' => '発注書', 'route' => 'orders.index', 'pattern' => 'orders.*'],
            ['label' => '案件管理', 'route' => 'projects.index', 'pattern' => 'projects.*'],
        ];
    @endphp

    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/80 backdrop-blur">
        <nav x-data="{ open: false }" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between gap-4">
                <a href="{{ route('dashboard.index') }}"
                   class="flex items-center gap-2 text-sm font-semibold tracking-tight text-slate-900">
                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-brand-600 text-xs font-bold text-white">N</span>
                    Novalumo Console
                </a>

                {{-- デスクトップ --}}
                <ul class="hidden items-center gap-1 lg:flex">
                    @foreach ($navigation as $item)
                        @php $active = request()->route()->named($item['pattern']); @endphp
                        <li>
                            <a href="{{ route($item['route']) }}"
                               @class([
                                   'rounded-lg px-3 py-2 text-sm transition',
                                   'bg-brand-50 font-semibold text-brand-800' => $active,
                                   'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $active,
                               ])>{{ $item['label'] }}</a>
                        </li>
                    @endforeach
                </ul>

                <div class="flex items-center gap-2">
                    <x-dropdown class="hidden lg:inline-block">
                        <x-slot:trigger>
                            <button type="button"
                                    class="flex items-center gap-2 rounded-lg py-1.5 pr-2 pl-1.5 text-sm text-slate-700 transition hover:bg-slate-100">
                                <img src="https://www.gravatar.com/avatar/{{ md5(session('email')) }}?s=56&d=mp"
                                     alt="" class="h-7 w-7 rounded-full bg-slate-200" width="28" height="28">
                                <span class="hidden max-w-32 truncate sm:block">{{ session('name') }}</span>
                                <i class="bi bi-chevron-down text-[10px] text-slate-400" aria-hidden="true"></i>
                            </button>
                        </x-slot:trigger>

                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="truncate text-sm font-medium text-slate-900">{{ session('name') }}</p>
                            <p class="truncate text-xs text-slate-500">{{ session('email') }}</p>
                        </div>

                        <x-dropdown-item :href="route('files.index')">Files</x-dropdown-item>
                        <x-dropdown-divider />
                        <p class="px-4 pt-2 pb-1 text-[10px] font-semibold tracking-wide text-slate-400 uppercase">Settings</p>
                        <x-dropdown-item :href="route('users.index')">User Management</x-dropdown-item>
                        <x-dropdown-item :href="route('customers.index')">Customer Management</x-dropdown-item>
                        <x-dropdown-item :href="route('settings.index')">Settings</x-dropdown-item>
                        <x-dropdown-divider />
                        <x-dropdown-item href="javascript:toBeLoggedOut.submit()">Logout</x-dropdown-item>
                    </x-dropdown>

                    {{-- モバイル: Bootstrap の navbar-toggler / collapse の置き換え --}}
                    <button type="button" @click="open = ! open" :aria-expanded="open" aria-label="メニューを開く"
                            class="rounded-lg p-2 text-slate-600 transition hover:bg-slate-100 lg:hidden">
                        <i class="bi text-lg" :class="open ? 'bi-x-lg' : 'bi-list'" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div x-show="open" x-cloak x-transition class="border-t border-slate-200 py-3 lg:hidden">
                <ul class="space-y-1">
                    @foreach ($navigation as $item)
                        @php $active = request()->route()->named($item['pattern']); @endphp
                        <li>
                            <a href="{{ route($item['route']) }}"
                               @class([
                                   'block rounded-lg px-3 py-2 text-sm',
                                   'bg-brand-50 font-semibold text-brand-800' => $active,
                                   'text-slate-600 hover:bg-slate-100' => ! $active,
                               ])>{{ $item['label'] }}</a>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-3 border-t border-slate-200 pt-3">
                    <p class="px-3 pb-2 text-xs text-slate-500">{{ session('name') }}</p>
                    <ul class="space-y-1">
                        <li><a href="{{ route('files.index') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">Files</a></li>
                        <li><a href="{{ route('users.index') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">User Management</a></li>
                        <li><a href="{{ route('customers.index') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">Customer Management</a></li>
                        <li><a href="{{ route('settings.index') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">Settings</a></li>
                        <li><a href="javascript:toBeLoggedOut.submit()" class="block rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 lg:px-8">
        @yield('page')
    </main>

    <form name="toBeLoggedOut" method="POST" action="{{ route('logout') }}">
        @csrf
    </form>
</body>

</html>
