@inject('Common', 'App\Lib\Common')
@extends('layouts/default')
@section('page')

<?php
$items = [];
?>
<style>
    .items {
        margin: 0;
        padding: 0;
    }
</style>

<div id="app">
    <form method="post" action="" autocomplete="off">

        {{-- control --}}
        <div class="row mb-3">
            <div class="col-12">
                <a href="./" class="btn btn-primary"><i class="bi bi-arrow-left-circle-fill"></i> 戻る</a>
                <a class="btn btn-primary"><i class="fa fa-floppy-o"></i> 保存する</a>
            </div>
        </div>
        {{-- 取引先 --}}
        <div class="row mb-3">
            <div class="col-12">
                <label class="form-label">取引先<span class="ms-1 badge bg-primary">必須</span></label>
            </div>
            <div class="col-md-6">
                <select name="destination" class="form-select" id="customer">
                    <option selected disabled>選択してください</option>
                    <option value="0">自社</option>
                    @foreach ($clients as $client)
                        <option value={{ $client['id'] }}>{{ $client['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="input-group">
                        <input type="text" name="responsible" placeholder="担当者" class="form-control">
                        <input type="text" name="honor_title" placeholder="御中" class="form-control" value="御中">
                    </div>
                </div>
            </div>
        </div>
        {{-- 日付 --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">発行日<span class="ms-1 badge bg-primary">必須</span></label>
                <input type="date" id="issued_date" name="issued_date" class="form-control" value="<?= date('Y-m-d');?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">有効期限</label>
                <input type="date" id="exp_date" name="exp_date" class="form-control">
            </div>
        </div>
        {{-- 番号・件名 --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">発注書番号<span class="ms-1 badge bg-primary">必須</span></label>
                <input type="text" name="estimate_no" class="form-control" value="<?= date('Ymd') ?>-xxx">
            </div>
            <div class="col-md-9">
                <label class="form-label">件名</label>
                <input type="text" name="title" class="form-control" v-model.trim="titleCount">
                <small>70</small>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <table class="document-table">
                    <thead>
                        <tr class="thead">
                            <th style="width: 3%; visibility: hidden; border: none;"></th>
                            <th style="width: 30%;">詳細</th>
                            <th style="width: 10%;">数量</th>
                            <th style="width: 10%;">単位</th>
                            <th style="width: 12%;">単価</th>
                            <th style="width: 15%;">税区分</th>
                            <th style="width: 20%;">金額</th>
                        </tr>
                    </thead>
                    <tbody class="main_tbody" id="sortable">
                        @for ($i = 0; $i < 5; $i++)
                        <tr class="sortable-tr">
                            <td class="action-cell"><span class="delete-row-button">×</span></td>
                            <td class="item-cell">
                                <input type="text" name="item_name[]" class="form-control ti-name">
                                <div class="items_box">
                                    <ul class="items">
                                    <?php foreach ($items as $item): ?>
                                        <li class="items_name" data-name="<?= $item['Item']['item_name']; ?>" data-unit="<?= $item['Item']['unit']; ?>" data-cost="<?= $item['Item']['cost']; ?>" data-tax="<?= $item['Item']['tax']; ?>"><?= $item['Item']['item_name']; ?> @<?= number_format($item['Item']['cost']); ?>円</li>
                                    <?php endforeach; ?>
                                    </ul>
                                </div>
                            </td>
                            <td>
                                <input type="text" name="qty[]" id={{"qty_".$i}} class="form-control ti-qty text-end">
                            </td>
                            <td>
                                <input type="text" name="unit[]" class="form-control ti-unit text-center" placeholder="単位" value="">
                            </td>
                            <td>
                                <input type="text" name="cost[]" id={{"cost_".$i}} class="form-control ti-cost text-end" value="">
                            </td>
                            <td>
                                <select name="tax[]" class="form-select ti-tax">
                                    @foreach ($Common->getTaxes() as $tax)
                                        <option value={{ $tax['id'] }}>{{ $tax['name'] }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control ti-sum text-end readonly" tabindex="-1" readonly>
                            </td>
                        </tr>
                        @endfor
                    </tbody>

                    <!-- 計算結果 -->
                    <tbody>
                    <tr class="sum-tr">
                        <td rowspan="3" style="border: none !important; vertical-align: top;"></td>
                        <td colspan="3" rowspan="3" style="border: none !important; vertical-align: top;">
                        <span href="#" onclick="addRow()" class="link" id="rowAddBtn"><i class="fa fa-plus"></i> 行の追加<span id="rowRemain"></span></span>
                        </td>
                        <td colspan="2" style="text-align: center;">小計</td>
                        <td><input type="text" id="subtotal" class="form-control text-end readonly" value="0" readonly tabindex="-1" value=""></td>
                    </tr>
                    <tr class="sum-tr">
                        <td colspan="2" style="text-align: center;">消費税</td>
                        <td><input type="text" id="taxTotal" class="form-control text-end readonly" value="0" readonly tabindex="-1" value=""></td>
                    </tr>
                    <tr class="sum-tr">
                        <td colspan="2" style="text-align: center;">合計</td>
                        <td><input type="text" name="price" id="totalPrice" class="form-control text-end readonly" value="0" readonly tabindex="-1"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-12">
                <label class="form-label">備考</label>
                <textarea class="form-control textarea" name="remarks" id="remarks" v-model.trim="remarksCount" style="height: 74px;"></textarea>
                <small>1000</small>
            </div>
        </div>

        {{-- 登録情報 --}}
        <input type="hidden" name="reg_uid" value="<?= ''?>">
        <input type="hidden" name="reg_datetime" value="<?= date('Y-m-d H:i:s');?>">
    </form>
</div><!--app-->

@endsection
