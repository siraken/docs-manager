@php
    // セル内の入力欄は罫線を表に任せ、下線だけを持たせる
    $cell = 'w-full bg-transparent px-1 py-1 text-sm text-slate-900 border-0 border-b border-dotted border-slate-300'
        . ' focus:border-solid focus:border-brand-600 focus:outline-none';
    $readonly = 'w-full bg-transparent px-1 py-1 text-right text-sm font-medium text-slate-700 border-0 focus:outline-none';
@endphp

{{-- 明細 1 行分。行の追加は order-form.ts がこのマークアップを <template> から
     複製して行うため、定義はこのファイルだけに置く。
     各欄は name 属性で引くので、行ごとの id は持たせていない。 --}}
<tr class="sortable-tr">
    <td class="action-cell">
        <span class="delete-row-button" role="button" aria-label="行を削除">&times;</span>
    </td>
    <td>
        <input type="text" name="item_name[]" class="{{ $cell }}">
    </td>
    <td>
        <input type="text" name="qty[]" class="{{ $cell }} text-right calc">
    </td>
    <td>
        <input type="text" name="unit[]" placeholder="単位" class="{{ $cell }} text-center">
    </td>
    <td>
        <input type="text" name="cost[]" class="{{ $cell }} text-right calc">
    </td>
    <td>
        <select name="tax[]" class="{{ $cell }} calc">
            <option value="1">10%</option>
            <option value="2">軽減8%</option>
            <option value="3">8%</option>
            <option value="4">5%</option>
            <option value="5">対象外</option>
        </select>
    </td>
    <td>
        <input type="text" name="price[]" class="{{ $readonly }}" tabindex="-1" readonly>
    </td>
</tr>
