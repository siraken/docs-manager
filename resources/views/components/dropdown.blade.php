@props(['align' => 'right', 'width' => 'w-56'])

{{-- Bootstrap の data-bs-toggle="dropdown" の置き換え。開閉は Alpine が持つ --}}
<div x-data="{ open: false }" @keydown.escape.window="open = false" {{ $attributes->class('relative inline-block') }}>
    <div @click="open = !open" class="contents">{{ $trigger }}</div>

    <div x-show="open"
         x-cloak
         @click.outside="open = false"
         @click="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-30 mt-2 {{ $width }} {{ $align === 'right' ? 'right-0 origin-top-right' : 'left-0 origin-top-left' }}
                overflow-hidden rounded-xl bg-white py-1 shadow-lg ring-1 ring-slate-900/5">
        {{ $slot }}
    </div>
</div>
