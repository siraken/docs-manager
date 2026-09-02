@extends('layouts/default')
@section('page')

{{-- 二段階認証の設定。

     シークレットはこの画面を開いたときに発行され、認証コードの検証が通るまで
     ユーザーのレコードには書かれない (セッションに一時保管される)。

     TODO: QR コード画像は生成していない。下の URI を認証アプリに手入力するか、
           コピーして読み込ませる必要がある。画像を出すなら QR エンコーダを
           足して Infrastructure 層の実装として差し込むこと。 --}}

<form method="post" action="" autocomplete="off" id="MainForm">
    <x-page-header title="二段階認証の設定">
        <x-slot:description>{{ $user->name }}（{{ $user->email }}）</x-slot:description>
        <x-slot:actions>
            <x-button :href="route('users.edit', ['id' => $user->id])" icon="arrow-left">戻る</x-button>
            <x-button type="submit" variant="primary" icon="check-lg">有効にする</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($errors->any())
        <div class="mb-6 max-w-md rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-md">
        <x-card class="space-y-5">
            @csrf

            @if ($setup->alreadyEnabled)
                <p class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800 ring-1 ring-amber-200">
                    このユーザーは既に二段階認証が有効です。ここで登録し直すと、いま使っている認証アプリの登録は無効になります。
                </p>
            @endif

            <div>
                <x-label>セットアップ用のキー</x-label>
                <p class="mt-1 font-mono text-sm break-all text-slate-900 select-all">{{ $setup->secret }}</p>
                <p class="mt-1 text-xs text-slate-400">認証アプリに手入力する場合はこのキーを使います</p>
            </div>

            <div>
                <x-label>otpauth URI</x-label>
                <p class="mt-1 font-mono text-xs break-all text-slate-500 select-all">{{ $setup->uri }}</p>
            </div>

            <div>
                <x-label for="code" required>認証コード</x-label>
                <x-input id="code" name="code" inputmode="numeric" autocomplete="one-time-code"
                         maxlength="6" placeholder="000000" required />
                <p class="mt-1 text-xs text-slate-400">認証アプリに表示された 6 桁を入力してください</p>
            </div>
        </x-card>
    </div>
</form>

@endsection
