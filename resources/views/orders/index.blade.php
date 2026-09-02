@extends('layouts/default')
@section('page')

{{-- slipSetter.status がトークンを document.getElementsByName('_token') から読む --}}
@csrf

<x-page-header title="発注書">
    <x-slot:actions>
        <x-button :href="route('orders.create')" variant="primary" icon="plus-lg">発注書を新しく作る</x-button>
        <x-button :href="route('orders.trash')" icon="trash">ごみ箱</x-button>
    </x-slot:actions>
</x-page-header>

@if (count($orders) === 0)
    <x-empty-state>データがありません</x-empty-state>
@else
    <x-table>
        <x-slot:head>
            <th class="px-4 py-3">ステータス</th>
            <th class="px-4 py-3">文書</th>
            <th class="px-4 py-3">発行日</th>
            <th class="px-4 py-3">有効期限</th>
            <th class="px-4 py-3 text-right">金額</th>
            <th class="px-4 py-3"><span class="sr-only">操作</span></th>
        </x-slot:head>

        @foreach ($orders as $row)
            <tr class="transition hover:bg-slate-50">
                <td class="px-4 py-3 align-middle">
                    <div class="flex flex-col gap-1">
                        <x-order-status type="issued" :id="$row['id']" :value="$row['is_issued']" />
                        <x-order-status type="ordered" :id="$row['id']" :value="$row['is_ordered']" />
                    </div>
                </td>
                <td class="px-4 py-3 align-middle">
                    <a href="{{ route('orders.edit', ['id' => $row['id']]) }}"
                       class="font-medium text-brand-700 hover:text-brand-900 hover:underline">
                        {{ $row['title'] ?: $row['customer_id'] }}
                    </a>
                    <p class="text-xs text-slate-400">#{{ $row['order_no'] }}</p>
                </td>
                <td class="px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular">
                    {{ date('Y/m/d', strtotime($row['issued_date'])) }}
                </td>
                <td class="px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular">
                    {{ !empty($row['exp_date']) ? date('Y/m/d', strtotime($row['exp_date'])) : '-' }}
                </td>
                <td class="px-4 py-3 text-right align-middle font-semibold whitespace-nowrap text-slate-900 tabular">
                    {{ number_format($row['total_price'] ?: 0) }}円
                </td>
                <td class="px-4 py-3 text-right align-middle">
                    <x-dropdown>
                        <x-slot:trigger>
                            <x-button size="sm" icon="gear-fill" aria-label="操作" />
                        </x-slot:trigger>

                        @if (!empty($row->note))
                            <x-dropdown-item disabled>{{ $row->note }}</x-dropdown-item>
                            <x-dropdown-divider />
                        @endif
                        <x-dropdown-item :href="route('orders.pdf', ['id' => $row['id']])">PDF出力</x-dropdown-item>
                        <x-dropdown-item :href="route('orders.delete', ['id' => $row['id']])">ごみ箱に入れる</x-dropdown-item>
                    </x-dropdown>
                </td>
            </tr>
        @endforeach
    </x-table>
@endif

@endsection
