@props(['top' => 'top-20'])

@php
    $status = session('flash_status');
    $tone = match ($status) {
        'danger' => 'bg-red-600',
        'success' => 'bg-green-600',
        'warning' => 'bg-amber-500',
        default => 'bg-slate-800',
    };
@endphp

@if (session('flash_message'))
    {{-- Bootstrap の toast の置き換え --}}
    <div x-data="{ show: true }"
         x-show="show"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         role="alert"
         aria-live="assertive"
         class="fixed {{ $top }} left-1/2 z-50 flex w-[min(28rem,calc(100vw-2rem))] -translate-x-1/2 items-start gap-3
                rounded-xl px-4 py-3 text-sm text-white shadow-lg {{ $tone }}">
        <i class="bi bi-{{ session('flash_icon') ?: 'exclamation-circle-fill' }} mt-0.5 shrink-0" aria-hidden="true"></i>
        <span class="flex-1">{{ session('flash_message') }}</span>
        <button type="button" @click="show = false" aria-label="閉じる"
                class="-mt-0.5 -mr-1 shrink-0 rounded p-1 text-white/70 transition hover:bg-white/10 hover:text-white">
            <i class="bi bi-x-lg text-xs" aria-hidden="true"></i>
        </button>
    </div>
@endif
