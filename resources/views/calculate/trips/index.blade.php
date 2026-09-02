@extends('layouts/default')
@section('page')

<x-page-header title="出張申請">
    <x-slot:actions>
        <x-button :href="route('trips.create')" variant="primary" icon="plus-lg">出張申請をする</x-button>

        <x-modal title="CSV取り込み">
            <x-slot:trigger>
                <x-button icon="upload">CSV取り込み</x-button>
            </x-slot:trigger>

            <form id="csv-import-form" action="{{ route('trips.import') }}" method="post"
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

@if (count($trips) === 0)
    <x-empty-state>申請がまだありません</x-empty-state>
@else
    <x-table>
        <x-slot:head>
            <th class="px-4 py-3">申請日</th>
            <th class="px-4 py-3">出張先</th>
            <th class="hidden px-4 py-3 sm:table-cell">目的</th>
            <th class="hidden px-4 py-3 sm:table-cell">出発日</th>
            <th class="px-4 py-3">申請者</th>
            <th class="px-4 py-3"><span class="sr-only">操作</span></th>
        </x-slot:head>

        @foreach ($trips as $row)
            <tr class="transition hover:bg-slate-50">
                <td class="px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular">{{ $row->applyDate }}</td>
                <td class="px-4 py-3 align-middle font-medium text-slate-900">
                    <a href="{{ route('trips.view', ['id' => $row->id]) }}"
                       class="text-brand-700 hover:text-brand-900 hover:underline">{{ $row->destination }}</a>
                </td>
                <td class="hidden px-4 py-3 align-middle text-slate-600 sm:table-cell">{{ $row->shortPurpose() }}</td>
                <td class="hidden px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular sm:table-cell">{{ $row->dateFrom }}</td>
                <td class="px-4 py-3 align-middle text-slate-600">{{ $row->applyPerson }}</td>
                <td class="px-4 py-3 text-right align-middle">
                    {{-- 移行前はここのドロップダウンに「ごみ箱に入れる」があり、
                         リンク先が発注書の削除ルート (orders.delete) を指していた。
                         出張申請に削除機能は無いため外してある。 --}}
                    <x-button :href="route('trips.pdf', ['id' => $row->id])" size="sm" icon="file-earmark-pdf">PDF</x-button>
                </td>
            </tr>
        @endforeach
    </x-table>
@endif

@endsection
