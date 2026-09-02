@props(['title' => null])

<div {{ $attributes->class('mb-6 flex flex-wrap items-center justify-between gap-3') }}>
    <div>
        @if ($title)
            <h1 class="text-xl font-semibold tracking-tight text-slate-900">{{ $title }}</h1>
        @endif
        @isset($description)
            <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
        @endisset
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
