@extends('layouts/default')
@section('page')

{{-- CakePHP から移植途中のまま残っていたビュー。h() は Laravel に存在しないため
     Blade の記法 (自動で HTML エスケープされる) に置き換えている。
     なお現時点で expenses.view のルートは存在せず、この画面には到達できない。 --}}

@php
    $rows = [
        '管理ID' => $expense->rel_id,
        '出張先' => $expense->dir,
        '目的' => $expense->purpose,
        '申請者' => $expense->apply_person,
        '交通費計' => $expense->trans_fee,
        'ガソリン代' => $expense->gas_fee,
        '日当計' => $expense->daily_pay,
        '宿泊費計' => $expense->acm_fee,
        '昼食計' => $expense->lunch_fee,
        '夕食計' => $expense->dinner_fee,
        '合計金額' => $expense->total_fee,
        '申請日' => $expense->apply_date,
        '出発日' => $expense->date_from,
        '帰着日' => $expense->date_to,
        '精算日' => $expense->pay_date,
    ];
@endphp

<x-page-header title="旅費精算 / 詳細">
    <x-slot:actions>
        <x-button :href="route('expenses.index')" icon="arrow-left">戻る</x-button>
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
