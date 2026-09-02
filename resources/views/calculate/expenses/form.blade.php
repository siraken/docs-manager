@extends('layouts/default')
@section('page')

@php
    // 費目。合計はここから導出するため入力欄を持たない
    $fees = [
        ['name' => 'trans_fee', 'label' => '交通費', 'value' => $expense->transportationFee],
        ['name' => 'acm_fee', 'label' => '宿泊費', 'value' => $expense->accommodationFee],
        ['name' => 'gas_fee', 'label' => 'ガソリン代', 'value' => $expense->gasFee],
        ['name' => 'lunch_fee', 'label' => '昼食代', 'value' => $expense->lunchFee],
        ['name' => 'dinner_fee', 'label' => '夕食代', 'value' => $expense->dinnerFee],
        ['name' => 'daily_pay', 'label' => '日当', 'value' => $expense->dailyAllowance],
    ];
@endphp

<form method="post">
    <x-page-header :title="$isNew ? '出張旅費 / 精算' : '出張旅費 / 精算の編集'">
        <x-slot:actions>
            <x-button :href="route('expenses.index')" icon="arrow-left">戻る</x-button>
            <x-button type="submit" variant="primary" icon="check-lg">保存する</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($errors->any())
        <div class="mb-6 max-w-2xl rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-2xl">
        <x-card class="space-y-5">
            @csrf

            <div>
                <x-label for="rel_id" required>管理ID</x-label>
                <x-input id="rel_id" name="rel_id" value="{{ old('rel_id', $expense->relId) }}" required />
            </div>

            <div>
                <x-label for="dir" required>出張先</x-label>
                <x-input id="dir" name="dir" value="{{ old('dir', $expense->destination) }}" required />
            </div>

            <div>
                <x-label for="purpose" required>目的</x-label>
                <x-textarea id="purpose" name="purpose" rows="6" required>{{ old('purpose', $expense->purpose) }}</x-textarea>
            </div>

            {{-- 移行前はここに「交通費計 / ガソリン代 / 日当計 / 宿泊費計 / 昼食計 /
                 夕食計 / 合計 / 精算日」を足す TODO が残っていて、入力欄が無いまま
                 PDF だけがこれらの費目を印字していた (常に空欄になっていた)。 --}}
            <div>
                <x-label>費目</x-label>
                <div class="grid gap-4 sm:grid-cols-3">
                    @foreach ($fees as $fee)
                        <div>
                            <x-label :for="$fee['name']">{{ $fee['label'] }}</x-label>
                            <div class="flex">
                                <span class="inline-flex items-center rounded-l-lg bg-slate-100 px-3 text-sm text-slate-500 ring-1 ring-inset ring-slate-300">¥</span>
                                <x-input type="number" min="0" :id="$fee['name']" :name="$fee['name']"
                                         value="{{ old($fee['name'], $fee['value']) }}" class="rounded-l-none" />
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-slate-400">合計は保存時に費目から計算します</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-label for="apply_date" required>申請日</x-label>
                    <x-input type="date" id="apply_date" name="apply_date" value="{{ old('apply_date', $expense->applyDate) }}" required />
                </div>
                <div>
                    <x-label for="pay_date" required>精算日</x-label>
                    <x-input type="date" id="pay_date" name="pay_date" value="{{ old('pay_date', $expense->payDate) }}" required />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-label for="date_from" required>出発日</x-label>
                    <x-input type="date" id="date_from" name="date_from" value="{{ old('date_from', $expense->dateFrom) }}" required />
                </div>
                <div>
                    <x-label for="date_to" required>帰着日</x-label>
                    <x-input type="date" id="date_to" name="date_to" value="{{ old('date_to', $expense->dateTo) }}" required />
                </div>
            </div>

            <div>
                <x-label for="apply_person" required>申請者氏名</x-label>
                <x-input id="apply_person" name="apply_person"
                         value="{{ old('apply_person', $expense->applyPerson ?: session('name')) }}" required />
            </div>
        </x-card>
    </div>
</form>

@endsection
