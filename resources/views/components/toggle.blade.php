@props(['label' => null, 'checked' => false])

{{-- チェックボックスをスイッチの見た目にする。peer で兄弟要素の状態を拾う --}}
<label class="inline-flex cursor-pointer items-center gap-2">
    <input type="checkbox" @checked($checked) {{ $attributes->class('peer sr-only') }}>
    <span class="relative h-5 w-9 shrink-0 rounded-full bg-slate-300 transition-colors
                 peer-checked:bg-brand-600
                 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-600
                 after:absolute after:top-0.5 after:left-0.5 after:h-4 after:w-4 after:rounded-full
                 after:bg-white after:shadow-sm after:transition-transform after:content-['']
                 peer-checked:after:translate-x-4"></span>
    @if ($label)<span class="text-sm text-slate-700">{{ $label }}</span>@endif
</label>
