@inject('Common', 'App\Lib\Common')
@extends('layouts/default')
@section('page')

<?php
$items = [];
?>

<form method="post" action="" autocomplete="off">
    {{-- control --}}
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('estimates.index') }}" class="btn btn-light border">戻る</a>
            <button type="submit" class="btn btn-light border">保存する</button>
        </div>
    </div>
    {{-- 取引先 --}}
    <div class="row mb-3">
        <div class="col-12">
            <label class="form-label">取引先<span class="ms-1 badge bg-danger">必須</span></label>
        </div>
        <div class="col-12">
            <div class="input-group">
                <select name="destination" class="form-select" id="customer" required>
                    <option value="" selected disabled>選択してください</option>
                    @foreach ($clients as $client)
                    <option value="{{ $client['id'] }}">{{ $client['name'] }}</option>
                    @endforeach
                </select>
                <input type="text" name="responsible" placeholder="担当者名" class="form-control">
                <input type="text" name="honor_title" placeholder="御中 / 様" class="form-control" value="御中">
            </div>
        </div>
    </div>
    {{-- 日付 --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">発行日<span class="ms-1 badge bg-danger">必須</span></label>
            <input type="date" id="issued_date" name="issued_date" class="form-control" value="<?= date('Y-m-d');?>"
                required>
        </div>
        <div class="col-md-6">
            <label class="form-label">有効期限</label>
            <input type="date" id="exp_date" name="exp_date" class="form-control">
        </div>
    </div>
    {{-- 番号・件名 --}}
    <div class="row mb-3">
        <div class="col-md-3">
            <label class="form-label">見積書番号<span class="ms-1 badge bg-danger">必須</span></label>
            <input type="text" name="estimate_no" class="form-control" value="<?= date('Ymd') ?>-xxx" required>
        </div>
        <div class="col-md-9">
            <label class="form-label">件名</label>
            <input type="text" name="title" class="form-control" v-model.trim="titleCount">
            <small>70</small>
        </div>
    </div>
    {{-- 明細入力 --}}
    <div class="row">
        <div class="col-12">
            <table class="document-table">
                <thead>
                    <tr class="thead">
                        <th style="width: 3%; visibility: hidden; border: none;"></th>
                        <th style="width: 32%;">詳細</th>
                        <th style="width: 12.5%;">数量</th>
                        <th style="width: 10%;">単位</th>
                        <th style="width: 12.5%;">単価</th>
                        <th style="width: 12%;">税区分</th>
                        <th style="width: 18%;">金額</th>
                    </tr>
                </thead>
                <tbody class="main_tbody" id="sortable">
                    @for ($i = 0; $i < 5; $i++) <tr class="sortable-tr">
                        <td class="action-cell"><span class="delete-row-button">×</span></td>
                        <td class="item-cell">
                            <input type="text" name="item_name[]" class="form-control">
                            <div class="items_box">
                                <ul class="items">
                                    <?php foreach ($items as $item): ?>
                                    <li class="items_name" data-name="<?= $item['Item']['item_name']; ?>"
                                        data-unit="<?= $item['Item']['unit']; ?>"
                                        data-cost="<?= $item['Item']['cost']; ?>"
                                        data-tax="<?= $item['Item']['tax']; ?>">
                                        <?= $item['Item']['item_name']; ?> @
                                        <?= number_format($item['Item']['cost']); ?>円
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </td>
                        <td>
                            <input type="text" name="qty[]" id="{{" qty_".$i}}" class="form-control text-end calc">
                        </td>
                        <td>
                            <input type="text" name="unit[]" class="form-control text-center" placeholder="単位" value="">
                        </td>
                        <td>
                            <input type="text" name="cost[]" id="{{" cost_".$i}}" class="form-control text-end calc"
                                value="">
                        </td>
                        <td>
                            <select name="tax[]" id="{{" tax_".$i}}" class="form-select calc">
                                <option value="1">10%</option>
                                <option value="2">軽減8%</option>
                                <option value="3">8%</option>
                                <option value="4">5%</option>
                                <option value="5">対象外</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="price[]" id="{{" price_".$i}}"
                                class="form-control text-end readonly" tabindex="-1" readonly>
                            <input type="hidden" id="{{" tax_price_".$i}}" readonly>
                        </td>
                        </tr>
                        @endfor
                </tbody>
                {{-- 計算結果 --}}
                <tbody>
                    <tr class="sum-tr">
                        <td rowspan="3" style="border: none !important; vertical-align: top;"></td>
                        <td colspan="3" rowspan="3" style="border: none !important; vertical-align: top;">
                            <span href="#" onclick="addRow()" class="btn btn-light border" id="rowAddBtn"><i
                                    class="bi-plus-lg me-1"></i>行の追加</span>
                        </td>
                        <td colspan="2" style="text-align: center;">小計</td>
                        <td><input type="text" name="subtotal" id="subtotal" class="form-control text-end readonly"
                                value="0" readonly tabindex="-1" value=""></td>
                    </tr>
                    <tr class="sum-tr">
                        <td colspan="2" style="text-align: center;">消費税</td>
                        <td><input type="text" name="taxTotal" id="taxTotal" class="form-control text-end readonly"
                                value="0" readonly tabindex="-1" value=""></td>
                    </tr>
                    <tr class="sum-tr">
                        <td colspan="2" style="text-align: center;">合計</td>
                        <td><input type="text" name="totalPrice" id="totalPrice" class="form-control text-end readonly"
                                value="0" readonly tabindex="-1"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    {{-- 備考欄 --}}
    <div class="row mb-3">
        <div class="col-12">
            <label class="form-label">備考</label>
            <textarea class="form-control textarea h-50" name="remarks" id="remarks"></textarea>
            <small>1000</small>
        </div>
    </div>

    {{-- 登録情報 --}}
    <input type="hidden" name="reg_uid" value="{{ '' }}">
    @csrf
</form>

@endsection
