@extends('layouts/default')
@section('page')

<form method="post" action="" autocomplete="off" id="MainForm">
    <x-page-header title="ユーザーの編集">
        <x-slot:actions>
            <x-button :href="route('users.index')" icon="arrow-left">戻る</x-button>
            <x-button :href="route('users.2fa', ['id' => $user->id])" icon="shield-lock">2FA</x-button>
            <x-button type="submit" variant="primary" icon="check-lg">保存する</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="max-w-2xl">
        <x-card class="space-y-5">
            @csrf

            <div>
                <x-label for="name" required>名前</x-label>
                <x-input id="name" name="name" value="{{ old('name', $user['name']) }}" required />
            </div>

            <div>
                <x-label for="email" required>メールアドレス</x-label>
                <x-input type="email" id="email" name="email" value="{{ old('email', $user['email']) }}" />
            </div>

            <div>
                <x-label for="password" required>パスワード</x-label>
                <x-input type="password" id="password" name="password" autocomplete="new-password" />
            </div>

            <div>
                <x-label for="nfc_number">NFC Card</x-label>
                <div class="flex">
                    <x-button class="rounded-r-none"
                              onclick="novalumo.setNfcNumber(document.getElementById('nfc_number'))">Scan</x-button>
                    <x-input type="password" id="nfc_number" name="nfc_serial_number" class="rounded-l-none"
                             value="{{ old('nfc_serial_number', $user['nfc_serial_number']) }}" />
                </div>
            </div>

            <div>
                <x-label for="nfc_pin">NFC PIN</x-label>
                <x-input type="password" id="nfc_pin" name="nfc_pin" autocomplete="new-password" />
            </div>

            <div>
                <x-label for="wallet_address">ウォレットアドレス</x-label>
                <div class="flex">
                    <x-button class="rounded-r-none" onclick="alert('TODO')">Scan</x-button>
                    <x-input id="wallet_address" name="wallet_address" class="rounded-l-none" placeholder="0x..."
                             value="{{ $user['wallet_address'] }}" />
                </div>
            </div>
        </x-card>
    </div>
</form>

@endsection
