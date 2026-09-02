@extends('layouts/auth')
@section('page')

<main class="w-full max-w-sm">
    <div class="mb-6 flex flex-col items-center gap-3">
        <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand-600 text-lg font-bold text-white">N</span>
        <h1 class="text-lg font-semibold tracking-tight text-slate-900">Novalumo Console</h1>
    </div>

    <x-card class="space-y-4">
        <form method="post" class="space-y-4">
            @csrf

            <div>
                <x-label for="email">メールアドレス</x-label>
                <x-input type="email" id="email" name="email" autocomplete="username" placeholder="name@example.com" />
            </div>

            <div>
                <x-label for="password">パスワード</x-label>
                <x-input type="password" id="password" name="password" autocomplete="current-password" />
            </div>

            <x-button type="submit" variant="primary" size="lg">サインイン</x-button>
        </form>
    </x-card>

    <p class="mt-6 text-center text-xs text-slate-400">&copy; Novalumo</p>
</main>

@endsection
