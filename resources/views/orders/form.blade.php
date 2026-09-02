@extends('layouts/default')
@section('page')

@php
    $sumInput = 'w-full bg-transparent px-1 py-1 text-right text-sm font-semibold text-slate-900 border-0 focus:outline-none';

    // 空行を含めて最低 5 行は出す (既存の明細がそれより多ければその数だけ)
    $blankRows = max(0, 5 - ($order === null ? 0 : count($order->lines)));
@endphp

<form method="post" action="" autocomplete="off">
    @csrf

    <x-page-header :title="$order === null ? '発注書の作成' : '発注書の編集'">
        <x-slot:actions>
            <x-button :href="route('orders.index')" icon="arrow-left">戻る</x-button>
            <x-button type="submit" variant="primary" icon="check-lg">保存する</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($errors->any())
        <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-6">
        <x-card class="space-y-5">
            {{-- 取引先 --}}
            <div>
                <x-label required>取引先</x-label>
                <div class="grid gap-2 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)]">
                    <x-select name="customer_id">
                        <option value="">選択してください</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}"
                                    @selected((int) old('customer_id', $order?->customerId) === $customer->id)>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </x-select>
                    <x-input name="responsible" placeholder="担当者名" value="{{ old('responsible', $order?->responsible) }}" />
                    <x-input name="honor_title" placeholder="御中 / 様" value="{{ old('honor_title', $order?->honorTitle ?? '御中') }}" />
                </div>
            </div>

            {{-- 日付 --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-label for="issued_date" required>発行日</x-label>
                    <x-input type="date" id="issued_date" name="issued_date"
                             value="{{ old('issued_date', $order?->issuedDate ?? date('Y-m-d')) }}" required />
                </div>
                <div>
                    <x-label for="exp_date">有効期限</x-label>
                    <x-input type="date" id="exp_date" name="exp_date" value="{{ old('exp_date', $order?->expDate) }}" />
                </div>
            </div>

            {{-- 番号・件名 --}}
            <div class="grid gap-4 sm:grid-cols-4">
                <div>
                    <x-label required>発注書番号</x-label>
                    <x-input name="order_no" value="{{ old('order_no', $order?->orderNo ?? date('Ymd') . '-xxx') }}" required />
                </div>
                <div class="sm:col-span-3">
                    <x-label>件名</x-label>
                    <x-input name="title" maxlength="70" value="{{ old('title', $order?->title) }}" />
                    <p class="mt-1 text-xs text-slate-400">70文字まで</p>
                </div>
            </div>
        </x-card>

        {{-- 明細入力 --}}
        <div class="overflow-x-auto">
            <table class="document-table w-full min-w-3xl">
                <thead>
                    <tr>
                        <th style="width: 3%" class="invisible border-none"></th>
                        <th style="width: 32%">詳細</th>
                        <th style="width: 12.5%">数量</th>
                        <th style="width: 10%">単位</th>
                        <th style="width: 12.5%">単価</th>
                        <th style="width: 12%">税区分</th>
                        <th style="width: 18%">金額</th>
                    </tr>
                </thead>
                <tbody class="main_tbody" id="sortable">
                    @if ($order !== null)
                        @foreach ($order->lines as $line)
                            <x-order-row :line="$line" :tax-options="$taxOptions" />
                        @endforeach
                    @endif

                    @for ($i = 0; $i < $blankRows; $i++)
                        <x-order-row :tax-options="$taxOptions" />
                    @endfor
                </tbody>

                {{-- 計算結果。表示用で、保存される金額はサーバー側で計算し直す --}}
                <tbody>
                    <tr class="sum-tr">
                        <td rowspan="3" class="sum-cell"></td>
                        <td colspan="3" rowspan="3" class="sum-cell">
                            <x-button id="rowAddBtn" onclick="addRow()" icon="plus-lg">行の追加</x-button>
                        </td>
                        <td colspan="2" class="text-center text-sm text-slate-600">小計</td>
                        <td><input type="text" name="subtotal" id="subtotal" class="{{ $sumInput }}"
                                   value="{{ $order?->subtotal ?? 0 }}" readonly tabindex="-1"></td>
                    </tr>
                    <tr class="sum-tr">
                        <td colspan="2" class="text-center text-sm text-slate-600">消費税</td>
                        <td><input type="text" name="taxTotal" id="taxTotal" class="{{ $sumInput }}"
                                   value="{{ $order?->tax ?? 0 }}" readonly tabindex="-1"></td>
                    </tr>
                    <tr class="sum-tr">
                        <td colspan="2" class="text-center text-sm font-semibold text-slate-700">合計</td>
                        <td><input type="text" name="totalPrice" id="totalPrice" class="{{ $sumInput }}"
                                   value="{{ $order?->total ?? 0 }}" readonly tabindex="-1"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- 備考欄 --}}
        <x-card>
            <x-label for="remarks">備考</x-label>
            <x-textarea name="remarks" id="remarks" rows="6" maxlength="1000">{{ old('remarks', $order?->remarks) }}</x-textarea>
            <p class="mt-1 text-xs text-slate-400">1000文字まで</p>
        </x-card>
    </div>
</form>

{{-- 行追加用のひな形。order-form.ts が content を複製して tbody に足す
     (行のマークアップを JS 側にも書くと二重管理になるため) --}}
<template id="order-row-template">
    <x-order-row :tax-options="$taxOptions" />
</template>

@endsection
