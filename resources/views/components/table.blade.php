{{-- 横スクロールはこのラッパーの中だけで起こす --}}
<div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
    <table {{ $attributes->class('min-w-full divide-y divide-slate-200 text-sm') }}>
        <thead class="bg-slate-50">
            <tr class="text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
                {{ $head }}
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            {{ $slot }}
        </tbody>
    </table>
</div>

@isset($empty)
    {{ $empty }}
@endisset
