@extends('layouts/default')
@section('page')

@php
    $rows = [
        '管理ID' => $trip->relId,
        '出張先' => $trip->destination,
        '目的' => $trip->purpose,
        '申請者' => $trip->applyPerson,
        '金額' => '¥' . $trip->priceLabel,
        '出発日' => $trip->dateFrom,
        '帰着日' => $trip->dateTo,
        '申請日' => $trip->applyDate,
    ];
@endphp

<x-page-header title="出張申請 / 詳細">
    <x-slot:actions>
        <x-button :href="route('trips.index')" icon="arrow-left">戻る</x-button>
        <x-button :href="route('trips.pdf', ['id' => $trip->id])" icon="file-earmark-pdf">PDF</x-button>
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
