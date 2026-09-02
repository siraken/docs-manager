@extends('layouts/default')
@section('page')

{{-- 自社情報。発注書 PDF の差出人欄とロゴ・社印に使われる。

     移行前は「設定項目はまだありません」と出すだけの画面で、settings テーブルは
     存在するのに参照するコードが無く、PDF の差出人欄はコントローラに直書きだった。 --}}

<form method="post" action="{{ route('settings.index') }}" autocomplete="off">
    <x-page-header title="Settings">
        <x-slot:description>発注書 PDF の差出人欄に使う自社情報です。</x-slot:description>
        <x-slot:actions>
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

    @if ($profile->isDefault)
        <div class="mb-6 max-w-2xl rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-amber-200">
            まだ保存されていません。表示しているのは移行前に PDF へ直書きされていた既定値です。
        </div>
    @endif

    <div class="max-w-2xl">
        <x-card class="space-y-5">
            @csrf

            <div>
                <x-label for="name" required>会社名</x-label>
                <x-input id="name" name="name" value="{{ old('name', $profile->name) }}" required />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-label for="zipcode">郵便番号</x-label>
                    <x-input id="zipcode" name="zipcode" value="{{ old('zipcode', $profile->zipcode) }}" />
                </div>
                <div>
                    <x-label for="tel_no">電話番号</x-label>
                    <x-input id="tel_no" name="tel_no" value="{{ old('tel_no', $profile->telNo) }}" />
                </div>
            </div>

            <div>
                <x-label for="address">住所</x-label>
                <x-textarea id="address" name="address" rows="3">{{ old('address', $profile->address) }}</x-textarea>
                <p class="mt-1 text-xs text-slate-400">改行するとPDFでも行が分かれます</p>
            </div>

            <div>
                <x-label for="rep">代表者名</x-label>
                <x-input id="rep" name="rep" value="{{ old('rep', $profile->representative) }}" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-label for="logo_url">ロゴ</x-label>
                    <x-input id="logo_url" name="logo_url" value="{{ old('logo_url', $profile->logoUrl) }}" />
                </div>
                <div>
                    <x-label for="com_stamp_url">社印</x-label>
                    <x-input id="com_stamp_url" name="com_stamp_url" value="{{ old('com_stamp_url', $profile->companyStampUrl) }}" />
                </div>
                <div>
                    <x-label for="rep_stamp_url">代表者印</x-label>
                    <x-input id="rep_stamp_url" name="rep_stamp_url" value="{{ old('rep_stamp_url', $profile->representativeStampUrl) }}" />
                </div>
                <div>
                    <x-label for="apply_stamp_url">承認印</x-label>
                    <x-input id="apply_stamp_url" name="apply_stamp_url" value="{{ old('apply_stamp_url', $profile->applyStampUrl) }}" />
                </div>
            </div>
            <p class="text-xs text-slate-400">
                画像は resources/ からの相対パスで指定します（例: img/Logo.png）。
                {{-- TODO: 画像のアップロード UI は未実装。いまはファイルを resources/img に
                     置いたうえでパスを手入力する必要がある。 --}}
            </p>
        </x-card>
    </div>
</form>

@endsection
