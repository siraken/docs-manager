@props([
    'variant' => 'secondary',
    'size' => 'md',
    'href' => null,
    'icon' => null,
])

@php
    $base = 'inline-flex items-center justify-center gap-1.5 rounded-lg font-medium whitespace-nowrap transition'
        . ' focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600'
        . ' disabled:pointer-events-none disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50';

    $variants = [
        'primary' => 'bg-brand-600 text-white shadow-sm hover:bg-brand-700 active:bg-brand-800',
        'secondary' => 'bg-white text-slate-700 ring-1 ring-slate-300 shadow-sm hover:bg-slate-50 active:bg-slate-100',
        'danger' => 'bg-red-600 text-white shadow-sm hover:bg-red-700 active:bg-red-800',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
    ];

    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-xs',
        'md' => 'px-3.5 py-2 text-sm',
        'lg' => 'w-full px-4 py-2.5 text-sm',
    ];

    $classes = trim($base . ' ' . ($variants[$variant] ?? $variants['secondary']) . ' ' . ($sizes[$size] ?? $sizes['md']));
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)<i class="bi bi-{{ $icon }}" aria-hidden="true"></i>@endif{{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button'])->class($classes) }}>
        @if ($icon)<i class="bi bi-{{ $icon }}" aria-hidden="true"></i>@endif{{ $slot }}
    </button>
@endif
