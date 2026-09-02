@props(['type' => 'text'])

@php
    $classes = 'block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm'
        . ' ring-1 ring-inset ring-slate-300 placeholder:text-slate-400'
        . ' focus:ring-2 focus:ring-inset focus:ring-brand-600'
        . ' disabled:bg-slate-50 disabled:text-slate-500'
        . ' read-only:bg-slate-50 read-only:text-slate-600'
        . ' file:mr-3 file:-my-2 file:-ml-3 file:rounded-l-lg file:border-0 file:bg-slate-100'
        . ' file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700';
@endphp

<input type="{{ $type }}" {{ $attributes->class($classes) }}>
