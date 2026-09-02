@extends('layouts/default')
@section('page')

{{-- Lumo Academy の問い合わせ一覧。

     移行前は AcademyController::index() が空 (何も返さない) で、ルートだけが
     登録されていた。登録用の register も保存していなかったため、そもそも
     表示するデータが存在しなかった。 --}}

<x-page-header title="Lumo Academy">
    <x-slot:description>フォームから届いた問い合わせです。</x-slot:description>
</x-page-header>

@if (count($inquiries) === 0)
    <x-empty-state>問い合わせはまだありません</x-empty-state>
@else
    <x-table>
        <x-slot:head>
            <th class="px-4 py-3">お名前</th>
            <th class="px-4 py-3">メールアドレス</th>
            <th class="px-4 py-3">内容</th>
        </x-slot:head>

        @foreach ($inquiries as $inquiry)
            <tr class="transition hover:bg-slate-50">
                <td class="px-4 py-3 align-top font-medium whitespace-nowrap text-slate-900">{{ $inquiry->name }}</td>
                <td class="px-4 py-3 align-top whitespace-nowrap text-slate-600">{{ $inquiry->email }}</td>
                <td class="px-4 py-3 align-top text-sm whitespace-pre-line text-slate-700">{{ $inquiry->inquiry }}</td>
            </tr>
        @endforeach
    </x-table>
@endif

@endsection
