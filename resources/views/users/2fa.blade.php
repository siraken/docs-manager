@extends('layouts/default')
@section('page')

<form method="post" action="" autocomplete="off" id="MainForm">
    <x-page-header title="二段階認証の設定">
        <x-slot:actions>
            <x-button :href="route('users.edit', ['id' => $user->id])" icon="arrow-left">戻る</x-button>
            <x-button type="submit" variant="primary" icon="check-lg">保存する</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="max-w-md">
        <x-card class="space-y-5">
            @csrf

            <div>
                <x-label>QR Code</x-label>
                <img src="{{ $qrCodeUrl }}" alt="二段階認証用の QR コード"
                     class="rounded-lg bg-white p-2 ring-1 ring-slate-200">
            </div>

            <div>
                <x-label for="code">OTP</x-label>
                <x-input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" required />
            </div>
        </x-card>
    </div>
</form>

@endsection
