@props(['type', 'id', 'value'])

@php
    // ViewModel から enum の値 (int) が渡る。既存データが文字列でも拾えるよう
    // int に寄せてから引く。
    $current = (int) $value;

    $labels = $type === 'issued'
        ? [0 => ['未発行', 'muted'], 1 => ['発行済み', 'on']]
        : [0 => ['未受注', 'muted'], 1 => ['受注済み', 'on'], 2 => ['失注', 'off']];

    [$label, $tone] = $labels[$current] ?? ['不明', 'muted'];

    $tones = [
        'muted' => 'bg-white text-slate-600 ring-slate-300 hover:bg-slate-50',
        'on' => 'bg-brand-600 text-white ring-brand-600 hover:bg-brand-700',
        'off' => 'bg-slate-700 text-white ring-slate-700 hover:bg-slate-800',
    ];
@endphp

<button type="button"
        onclick="slipSetter.status(this, '{{ $type }}', {{ $id }})"
        class="inline-flex w-24 items-center justify-center gap-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset transition select-none active:scale-95 {{ $tones[$tone] }}">
    @if ($current === 1)
        <i class="bi bi-check-lg text-[10px]" aria-hidden="true"></i>
    @endif
    {{ $label }}
</button>
