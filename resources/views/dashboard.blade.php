@extends('layouts/default')
@section('page')

@php
    $shortcuts = [
        ['label' => '出張申請', 'description' => '出張の申請書を作成する', 'route' => 'trips.index', 'icon' => 'airplane'],
        ['label' => '出張旅費精算', 'description' => '出張にかかった費用を精算する', 'route' => 'expenses.index', 'icon' => 'receipt'],
        ['label' => '発注書作成', 'description' => '取引先向けの発注書を作る', 'route' => 'orders.index', 'icon' => 'file-earmark-text'],
    ];
@endphp

<x-page-header title="Welcome, {{ session('name') }}">
    <x-slot:description>よく使う機能へのショートカットです。</x-slot:description>
</x-page-header>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ($shortcuts as $shortcut)
        <a href="{{ route($shortcut['route']) }}"
           class="group rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md hover:ring-brand-300">
            <span class="mb-3 grid h-10 w-10 place-items-center rounded-lg bg-brand-50 text-brand-700 transition group-hover:bg-brand-600 group-hover:text-white">
                <i class="bi bi-{{ $shortcut['icon'] }}" aria-hidden="true"></i>
            </span>
            <p class="font-medium text-slate-900">{{ $shortcut['label'] }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $shortcut['description'] }}</p>
        </a>
    @endforeach
</div>

@endsection
