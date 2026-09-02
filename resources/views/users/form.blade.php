@extends('layouts/default')
@section('page')

<form method="post" action="" autocomplete="off" id="MainForm">
    <x-page-header :title="$isNew ? 'ユーザーの新規登録' : 'ユーザーの編集'">
        <x-slot:actions>
            <x-button :href="route('users.index')" icon="arrow-left">戻る</x-button>

            {{-- 移行前はここを無条件に出していたため、新規登録画面では
                 route('users.2fa', ['id' => null]) の組み立てに失敗して 500 になっていた。
                 保存前のユーザーには 2FA を設定できないので、編集時だけ出す。 --}}
            @if (! $isNew)
                <x-button :href="route('users.2fa', ['id' => $user->id])" icon="shield-lock">2FA</x-button>
            @endif

            <x-button type="submit" variant="primary" icon="check-lg">保存する</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($errors->any())
        <div class="mb-6 max-w-2xl rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-2xl">
        <x-card class="space-y-5">
            @csrf

            <div>
                <x-label for="name" required>名前</x-label>
                <x-input id="name" name="name" value="{{ old('name', $user->name) }}" required />
            </div>

            <div>
                <x-label for="email" required>メールアドレス</x-label>
                <x-input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required />
            </div>

            <div>
                <x-label for="password" :required="$isNew">パスワード</x-label>
                <x-input type="password" id="password" name="password" autocomplete="new-password" />
                @unless ($isNew)
                    <p class="mt-1 text-xs text-slate-400">空のままにすると現在のパスワードを変更しません</p>
                @endunless
            </div>

            @unless ($isNew)
                <div>
                    <x-label for="nfc_number">NFC Card</x-label>
                    <div class="flex">
                        <x-button class="rounded-r-none"
                                  onclick="novalumo.setNfcNumber(document.getElementById('nfc_number'))">Scan</x-button>
                        <x-input id="nfc_number" name="nfc_serial_number" class="rounded-l-none"
                                 value="{{ old('nfc_serial_number', $user->nfcSerialNumber) }}" />
                    </div>
                    <p class="mt-1 text-xs text-slate-400">空にすると NFC ログインを無効にします</p>
                </div>

                <div>
                    <x-label for="nfc_pin">NFC PIN</x-label>
                    <x-input type="password" id="nfc_pin" name="nfc_pin" autocomplete="new-password" />
                    <p class="mt-1 text-xs text-slate-400">空のままにすると現在の PIN を変更しません</p>
                </div>

                <div>
                    <x-label for="wallet_address">ウォレットアドレス</x-label>
                    <x-input id="wallet_address" name="wallet_address" placeholder="0x..."
                             value="{{ old('wallet_address', $user->walletAddress) }}" />
                    <p class="mt-1 text-xs text-slate-400">0x で始まる 42 文字</p>
                </div>
            @endunless
        </x-card>
    </div>
</form>

@endsection
