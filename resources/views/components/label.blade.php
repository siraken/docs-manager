@props(['required' => false])

<label {{ $attributes->class('mb-1.5 flex items-center gap-1.5 text-sm font-medium text-slate-700') }}>
    {{ $slot }}
    @if ($required)
        <span class="rounded bg-red-50 px-1.5 py-0.5 text-[10px] font-semibold text-red-700 ring-1 ring-inset ring-red-200">必須</span>
    @endif
</label>
