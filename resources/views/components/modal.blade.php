@props(['title' => null, 'maxWidth' => 'sm:max-w-lg'])

{{-- Bootstrap の modal の置き換え。トリガーは trigger スロットに渡す --}}
<div x-data="{ open: false }" @keydown.escape.window="open = false" class="contents">
    <div @click="open = true" class="contents">{{ $trigger }}</div>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 sm:items-center">
        {{-- 背景。クリックで閉じる --}}
        <div x-show="open" x-transition.opacity @click="open = false" class="fixed inset-0 bg-slate-900/50"></div>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-2 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-2 sm:scale-95"
             role="dialog"
             aria-modal="true"
             class="relative w-full {{ $maxWidth }} overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-slate-900/5">
            @if ($title)
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
                    <button type="button" @click="open = false" aria-label="閉じる"
                            class="rounded-md p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                        <i class="bi bi-x-lg text-sm" aria-hidden="true"></i>
                    </button>
                </div>
            @endif

            <div class="px-5 py-4">{{ $slot }}</div>

            @isset($footer)
                <div class="flex items-center justify-between gap-3 border-t border-slate-200 bg-slate-50 px-5 py-3">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
