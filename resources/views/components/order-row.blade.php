@props(['index', 'items' => []])

@php
    // セル内の入力欄は罫線を表に任せ、下線だけを持たせる
    $cell = 'w-full bg-transparent px-1 py-1 text-sm text-slate-900 border-0 border-b border-dotted border-slate-300'
        . ' focus:border-solid focus:border-brand-600 focus:outline-none';
    $readonly = 'w-full bg-transparent px-1 py-1 text-right text-sm font-medium text-slate-700 border-0 focus:outline-none';
@endphp

<tr class="sortable-tr">
    <td class="action-cell">
        <span class="delete-row-button" role="button" aria-label="行を削除">&times;</span>
    </td>
    <td class="item-cell">
        <input type="text" name="item_name[]" class="{{ $cell }}">
        <div class="items_box">
            <ul class="items">
                @foreach ($items as $item)
                    <li class="items_name"
                        data-name="{{ $item['Item']['item_name'] }}"
                        data-unit="{{ $item['Item']['unit'] }}"
                        data-cost="{{ $item['Item']['cost'] }}"
                        data-tax="{{ $item['Item']['tax'] }}">
                        {{ $item['Item']['item_name'] }} @ {{ number_format($item['Item']['cost']) }}円
                    </li>
                @endforeach
            </ul>
        </div>
    </td>
    <td>
        <input type="text" name="qty[]" id="qty_{{ $index }}" class="{{ $cell }} text-right calc">
    </td>
    <td>
        <input type="text" name="unit[]" placeholder="単位" class="{{ $cell }} text-center">
    </td>
    <td>
        <input type="text" name="cost[]" id="cost_{{ $index }}" class="{{ $cell }} text-right calc">
    </td>
    <td>
        <select name="tax[]" id="tax_{{ $index }}" class="{{ $cell }} calc">
            <option value="1">10%</option>
            <option value="2">軽減8%</option>
            <option value="3">8%</option>
            <option value="4">5%</option>
            <option value="5">対象外</option>
        </select>
    </td>
    <td>
        <input type="text" name="price[]" id="price_{{ $index }}" class="{{ $readonly }}" tabindex="-1" readonly>
        <input type="hidden" id="tax_price_{{ $index }}" readonly>
    </td>
</tr>
