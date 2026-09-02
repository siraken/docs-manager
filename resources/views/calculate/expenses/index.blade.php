@extends('layouts/default')
@section('page')

<x-page-header title="出張旅費精算">
    <x-slot:actions>
        <x-button :href="route('expenses.create')" variant="primary" icon="plus-lg">旅費精算をする</x-button>

        <x-modal title="CSV取り込み">
            <x-slot:trigger>
                <x-button icon="upload">CSV取り込み</x-button>
            </x-slot:trigger>

            <form id="csv-import-form" action="{{ route('expenses.import') }}" method="post"
                  enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <x-label for="csvFormFile">CSVを選択してください</x-label>
                    <x-input type="file" id="csvFormFile" name="csv" accept=".csv" required />
                </div>
                <input type="hidden" name="header" value="0">
                <x-toggle name="header" value="1" checked label="ヘッダーあり" />
            </form>

            <x-slot:footer>
                <p class="text-xs text-slate-500">1行目を見出しとして読み飛ばすかを選べます</p>
                <x-button type="submit" form="csv-import-form" variant="primary">取り込み</x-button>
            </x-slot:footer>
        </x-modal>
    </x-slot:actions>
</x-page-header>

@if (count($expenses) === 0)
    <x-empty-state>精算がまだありません</x-empty-state>
@else
    <x-table>
        <x-slot:head>
            <th class="px-4 py-3">申請日</th>
            <th class="px-4 py-3">出張先</th>
            <th class="hidden px-4 py-3 sm:table-cell">目的</th>
            <th class="hidden px-4 py-3 sm:table-cell">精算日</th>
            <th class="px-4 py-3">申請者</th>
            <th class="px-4 py-3"><span class="sr-only">操作</span></th>
        </x-slot:head>

        @foreach ($expenses as $expense)
            <tr class="transition hover:bg-slate-50">
                <td class="px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular">{{ $expense->apply_date }}</td>
                <td class="px-4 py-3 align-middle font-medium text-slate-900">{{ $expense->dir }}</td>
                <td class="hidden px-4 py-3 align-middle text-slate-600 sm:table-cell">{{ $expense->purpose }}</td>
                <td class="hidden px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular sm:table-cell">{{ $expense->pay_date }}</td>
                <td class="px-4 py-3 align-middle text-slate-600">{{ $expense->apply_person }}</td>
                <td class="px-4 py-3 text-right align-middle">
                    <x-button :href="route('expenses.pdf', $expense->id)" size="sm" icon="eye">PDF</x-button>
                </td>
            </tr>
        @endforeach
    </x-table>
@endif

@endsection
