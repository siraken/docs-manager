@extends('layouts/default')
@section('page')

<x-page-header title="ファイル">
    <x-slot:description>ファイルをアップロードして共有します。</x-slot:description>
</x-page-header>

<div class="mb-6 max-w-2xl">
    <x-card>
        <form action="{{ route('files.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-label for="name">お名前</x-label>
                    <x-input id="name" name="name" />
                </div>
                <div>
                    <x-label for="email">メールアドレス</x-label>
                    <x-input type="email" id="email" name="email" />
                </div>
            </div>

            <div>
                <x-label for="file">ファイルを選択してください</x-label>
                <x-input type="file" id="file" name="file" />
            </div>

            <x-button type="submit" variant="primary" icon="upload">アップロード</x-button>
        </form>
    </x-card>
</div>

{{-- ログイン中のみ一覧を出す --}}
@if (session('email'))
    @if (count($files) === 0)
        <x-empty-state>アップロードされたファイルはありません</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th class="px-4 py-3">ファイル名</th>
                <th class="px-4 py-3"><span class="sr-only">操作</span></th>
            </x-slot:head>

            @foreach ($files as $file)
                <tr class="transition hover:bg-slate-50">
                    <td class="px-4 py-3 align-middle font-medium break-all text-slate-900">{{ $file }}</td>
                    <td class="px-4 py-3 text-right align-middle">
                        <div class="flex justify-end gap-2">
                            <x-button :href="route('files.download', ['file' => $file])" size="sm" icon="download">ダウンロード</x-button>
                            <form action="{{ route('files.delete', ['file' => $file]) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" size="sm" variant="danger" icon="trash">削除</x-button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif
@endif

@endsection
