@props(['href' => null, 'disabled' => false])

@php
    $classes = 'block w-full px-4 py-2 text-left text-sm transition';
    $classes .= $disabled
        ? ' cursor-default text-slate-400'
        : ' text-slate-700 hover:bg-slate-50 hover:text-slate-900';
@endphp

@if ($href && ! $disabled)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <div {{ $attributes->class($classes) }}>{{ $slot }}</div>
@endif
