@extends('layouts/default')
@section('page')

@php
    $rows = [
        '管理ID' => $expense->relId,
        '出張先' => $expense->destination,
        '目的' => $expense->purpose,
        '申請者' => $expense->applyPerson,
        '交通費' => '¥' . number_format($expense->transportationFee),
        'ガソリン代' => '¥' . number_format($expense->gasFee),
        '日当' => '¥' . number_format($expense->dailyAllowance),
        '宿泊費' => '¥' . number_format($expense->accommodationFee),
        '昼食代' => '¥' . number_format($expense->lunchFee),
        '夕食代' => '¥' . number_format($expense->dinnerFee),
        '合計金額' => '¥' . $expense->totalFeeLabel,
        '申請日' => $expense->applyDate,
        '出発日' => $expense->dateFrom,
        '帰着日' => $expense->dateTo,
        '精算日' => $expense->payDate,
    ];
@endphp

<x-page-header title="旅費精算 / 詳細">
    <x-slot:actions>
        <x-button :href="route('expenses.index')" icon="arrow-left">戻る</x-button>
        <x-button :href="route('expenses.edit', ['id' => $expense->id])" icon="pencil">編集</x-button>
        <x-button :href="route('expenses.pdf', ['id' => $expense->id])" icon="file-earmark-pdf">PDF</x-button>
    </x-slot:actions>
</x-page-header>

<div class="max-w-2xl">
    <x-card class="p-0">
        <dl class="divide-y divide-slate-100">
            @foreach ($rows as $label => $value)
                <div class="grid grid-cols-3 gap-4 px-6 py-3.5">
                    <dt class="text-sm font-medium text-slate-500">{{ $label }}</dt>
                    <dd class="col-span-2 text-sm whitespace-pre-line text-slate-900">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </x-card>
</div>

@endsection
