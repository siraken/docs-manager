@extends('layouts/default')
@section('page')

{{-- 発注書の詳細。

     移行前のこのファイルは CakePHP のビューがそのまま残っていて、1 行目の
     $this->Html->url() で落ちる状態だった (しかも中身は見積書のもので、
     PDF をファイルとして書き出してから iframe で表示していた)。
     PDF の生成はサーバー側 (RenderOrderPdfUseCase) に移したので、ここでは
     内容の表示とプレビューだけを行う。 --}}

@php
    $info = [
        '発注書番号' => '#' . $order->orderNo,
        '取引先' => trim($order->customerName . ' ' . $order->responsible . ' ' . $order->honorTitle),
        '件名' => $order->title ?: '-',
        '発注金額' => $order->totalLabel . '円',
        '発行日' => $order->issuedDateLabel,
        '有効期限' => $order->expDateLabel,
    ];
@endphp

<x-page-header :title="$order->displayName()">
    <x-slot:description>#{{ $order->orderNo }}{{ $order->isDeleted ? '（ごみ箱）' : '' }}</x-slot:description>
    <x-slot:actions>
        <x-button :href="route('orders.index')" icon="arrow-left">戻る</x-button>
        <x-button :href="route('orders.edit', ['id' => $order->id])" icon="pencil">編集</x-button>
        <x-button :href="route('orders.pdf', ['id' => $order->id])" icon="file-earmark-pdf">PDF</x-button>
        <x-button :href="route('orders.csv', ['id' => $order->id])" icon="download">CSV</x-button>
    </x-slot:actions>
</x-page-header>

<div class="space-y-6">
    <div class="grid gap-6 lg:grid-cols-2">
        <x-card class="p-0">
            <dl class="divide-y divide-slate-100">
                @foreach ($info as $label => $value)
                    <div class="grid grid-cols-3 gap-4 px-6 py-3.5">
                        <dt class="text-sm font-medium text-slate-500">{{ $label }}</dt>
                        <dd class="col-span-2 text-sm text-slate-900">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-card>

        <x-card>
            <x-label>社内メモ</x-label>
            <p class="mt-1 min-h-24 text-sm whitespace-pre-line text-slate-700">{{ $order->note ?: '（メモはありません）' }}</p>
            {{-- TODO: メモの編集は未実装。移行前の CakePHP ビューには保存用の
                 ajax があったが、対応するエンドポイントは Laravel 側に無い。
                 order_headers.note への保存はドメイン側 (Order::changeNote) に
                 用意してあるので、フォームとルートを足せば繋がる。 --}}
        </x-card>
    </div>

    <div>
        <h2 class="mb-3 text-sm font-semibold text-slate-700">明細</h2>

        @if (count($order->lines) === 0)
            <x-empty-state>明細がありません</x-empty-state>
        @else
            <x-table>
                <x-slot:head>
                    <th class="px-4 py-3">品名</th>
                    <th class="px-4 py-3 text-right">数量</th>
                    <th class="px-4 py-3 text-right">単価</th>
                    <th class="px-4 py-3">税区分</th>
                    <th class="px-4 py-3 text-right">金額(税込)</th>
                </x-slot:head>

                @foreach ($order->lines as $line)
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-900">{{ $line->itemName }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap text-slate-600 tabular">
                            {{ number_format($line->quantity) }}{{ $line->unit }}
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap text-slate-600 tabular">
                            {{ number_format($line->unitCost) }}
                        </td>
                        <td class="px-4 py-3"><x-badge>{{ $line->taxLabel }}</x-badge></td>
                        <td class="px-4 py-3 text-right font-medium whitespace-nowrap text-slate-900 tabular">
                            {{ number_format($line->total) }}
                        </td>
                    </tr>
                @endforeach

                <tr class="bg-slate-50">
                    <td colspan="4" class="px-4 py-3 text-right text-sm text-slate-600">小計</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap text-slate-900 tabular">{{ number_format($order->subtotal) }}</td>
                </tr>
                <tr class="bg-slate-50">
                    <td colspan="4" class="px-4 py-3 text-right text-sm text-slate-600">消費税</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap text-slate-900 tabular">{{ number_format($order->tax) }}</td>
                </tr>
                <tr class="bg-slate-50 font-semibold">
                    <td colspan="4" class="px-4 py-3 text-right text-sm text-slate-700">合計</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap text-slate-900 tabular">{{ $order->totalLabel }}</td>
                </tr>
            </x-table>
        @endif
    </div>

    @if ($order->remarks)
        <x-card>
            <x-label>備考</x-label>
            <p class="mt-1 text-sm whitespace-pre-line text-slate-700">{{ $order->remarks }}</p>
        </x-card>
    @endif
</div>

@endsection
