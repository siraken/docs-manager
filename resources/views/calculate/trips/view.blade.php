@extends('layouts/default')
@section('page')

{{-- CakePHP から移植途中のまま残っていたビュー。h() や $this->Number は Laravel に
     存在しないため Blade の記法 (自動で HTML エスケープされる) に置き換えている。
     なお現時点で trips.view のルートは存在せず、この画面には到達できない。 --}}

@php
    $rows = [
        '管理ID' => $trip->rel_id,
        '出張先' => $trip->dir,
        '目的' => $trip->purpose,
        '申請者' => $trip->apply_person,
        '金額' => '¥' . number_format($trip->price),
        '出発日' => date('Y年m月d日', strtotime($trip->date_from)),
        '帰着日' => date('Y年m月d日', strtotime($trip->date_to)),
        '申請日' => date('Y年m月d日', strtotime($trip->apply_date)),
    ];
@endphp

<x-page-header title="出張申請 / 詳細">
    <x-slot:actions>
        <x-button :href="route('trips.index')" icon="arrow-left">戻る</x-button>
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
