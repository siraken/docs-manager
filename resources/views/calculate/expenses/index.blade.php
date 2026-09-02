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
            <th class="px-4 py-3 text-right">合計</th>
            <th class="px-4 py-3"><span class="sr-only">操作</span></th>
        </x-slot:head>

        @foreach ($expenses as $expense)
            <tr class="transition hover:bg-slate-50">
                <td class="px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular">{{ $expense->applyDate }}</td>
                <td class="px-4 py-3 align-middle font-medium text-slate-900">
                    <a href="{{ route('expenses.view', ['id' => $expense->id]) }}"
                       class="text-brand-700 hover:text-brand-900 hover:underline">{{ $expense->destination }}</a>
                </td>
                <td class="hidden px-4 py-3 align-middle text-slate-600 sm:table-cell">{{ $expense->shortPurpose() }}</td>
                <td class="hidden px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular sm:table-cell">{{ $expense->payDate }}</td>
                <td class="px-4 py-3 align-middle text-slate-600">{{ $expense->applyPerson }}</td>
                <td class="px-4 py-3 text-right align-middle font-semibold whitespace-nowrap text-slate-900 tabular">
                    ￥{{ $expense->totalFeeLabel }}
                </td>
                <td class="px-4 py-3 text-right align-middle">
                    <div class="flex justify-end gap-2">
                        <x-button :href="route('expenses.edit', ['id' => $expense->id])" size="sm" icon="pencil">編集</x-button>
                        <x-button :href="route('expenses.pdf', ['id' => $expense->id])" size="sm" icon="file-earmark-pdf">PDF</x-button>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table>
@endif

@endsection
