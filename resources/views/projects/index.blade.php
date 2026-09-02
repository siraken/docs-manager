@extends('layouts/default')
@section('page')

<x-page-header title="案件管理">
    <x-slot:actions>
        <x-button :href="route('projects.create')" variant="primary" icon="plus-lg">新規案件</x-button>
        <x-button :href="route('projects.analysis')" icon="graph-up">分析</x-button>
    </x-slot:actions>
</x-page-header>

@if (count($projects) === 0)
    <x-empty-state>案件がまだありません</x-empty-state>
@else
    <x-table>
        <x-slot:head>
            <th class="px-4 py-3">案件名</th>
            <th class="px-4 py-3">取引先</th>
            <th class="px-4 py-3">状態</th>
            <th class="px-4 py-3">開始日</th>
            <th class="px-4 py-3">終了日</th>
            <th class="px-4 py-3">支払日</th>
            <th class="px-4 py-3 text-right">請求金額</th>
        </x-slot:head>

        @foreach ($projects as $row)
            <tr class="transition hover:bg-slate-50">
                <td class="px-4 py-3">
                    <a href="{{ route('projects.edit', ['id' => $row->id]) }}"
                       class="font-medium text-brand-700 hover:text-brand-900 hover:underline">{{ $row->name }}</a>
                </td>
                <td class="px-4 py-3 text-slate-600">{{ $row->clientId }}</td>
                <td class="px-4 py-3"><x-badge>{{ $row->status }}</x-badge></td>
                <td class="px-4 py-3 whitespace-nowrap text-slate-600 tabular">{{ $row->startDateLabel }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-slate-600 tabular">{{ $row->endDateLabel }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-slate-600 tabular">{{ $row->paymentDateLabel }}</td>
                <td class="px-4 py-3 text-right font-semibold whitespace-nowrap text-slate-900 tabular">￥{{ $row->priceLabel }}</td>
            </tr>
        @endforeach
    </x-table>
@endif

@endsection
