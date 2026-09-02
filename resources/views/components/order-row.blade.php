@props(['line' => null, 'taxOptions' => []])

@php
    // セル内の入力欄は罫線を表に任せ、下線だけを持たせる
    $cell = 'w-full bg-transparent px-1 py-1 text-sm text-slate-900 border-0 border-b border-dotted border-slate-300'
        . ' focus:border-solid focus:border-brand-600 focus:outline-none';
    $readonly = 'w-full bg-transparent px-1 py-1 text-right text-sm font-medium text-slate-700 border-0 focus:outline-none';
@endphp

{{-- 明細 1 行分。行の追加は order-form.ts がこのマークアップを <template> から
     複製して行うため、定義はこのファイルだけに置く。
     各欄は name 属性で引くので、行ごとの id は持たせていない。

     $line が渡された場合は既存の明細を描く (編集画面)。移行前のフォームは
     $header / $details を受け取っていながら一切使っておらず、編集画面を開いても
     空のフォームが出ていた。 --}}
<tr class="sortable-tr">
    <td class="action-cell">
        <span class="delete-row-button" role="button" aria-label="行を削除">&times;</span>
    </td>
    <td>
        <input type="text" name="item_name[]" class="{{ $cell }}" value="{{ $line?->itemName }}">
    </td>
    <td>
        <input type="text" name="qty[]" class="{{ $cell }} text-right calc" value="{{ $line?->quantity }}">
    </td>
    <td>
        <input type="text" name="unit[]" placeholder="単位" class="{{ $cell }} text-center" value="{{ $line?->unit }}">
    </td>
    <td>
        <input type="text" name="cost[]" class="{{ $cell }} text-right calc" value="{{ $line?->unitCost }}">
    </td>
    <td>
        <select name="tax[]" class="{{ $cell }} calc">
            @foreach ($taxOptions as $value => $label)
                <option value="{{ $value }}" @selected($line !== null && $line->taxId === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </td>
    <td>
        {{-- 表示専用。保存する金額はサーバー側で計算し直すため送っても使われない --}}
        <input type="text" name="price[]" class="{{ $readonly }}" tabindex="-1" readonly value="{{ $line?->total }}">
    </td>
</tr>
