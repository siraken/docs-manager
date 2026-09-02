@php
    $classes = 'block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm'
        . ' ring-1 ring-inset ring-slate-300 placeholder:text-slate-400'
        . ' focus:ring-2 focus:ring-inset focus:ring-brand-600';
@endphp

<textarea {{ $attributes->merge(['rows' => 4])->class($classes) }}>{{ $slot }}</textarea>
