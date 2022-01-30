@extends('layouts/default')
@section('page')

<?php

$months = array();
for ($i=1; $i <= 12; $i++) {
	$val = $i;
	$months[$val] = str_pad($val, 2, "0", STR_PAD_LEFT);
}

$items = [];
?>

<style>
.text-center { text-align: center; }
.text-right { text-align: right; }

.mb-1 { margin-bottom: 5px; }
.mb-3 { margin-bottom: 1em; }

.title { border-bottom: solid 1px #ccc; font-size: 120%; }
.link { color: #00acc1; text-decoration: none; cursor: pointer; }

.input {
  display: block;
  width: 100%;
  height: calc(1.5em + .75rem + 2px);
  padding: .375rem .75rem;
  font-size: 1rem;
  line-height: 1.5;
  background: #fff !important;
  border: 1px solid #ced4da;
  transition: .15s;
}
.input:focus {
  border-color: #80bdff;
  outline: 0;
  border: solid 1px #00acc1;
}

.table-input {
  display: block;
  width: 100%;
  height: calc(1.5em + .75rem + 2px);
  padding: .375rem .75rem;
  font-size: 1rem;
  line-height: 1.5;
  background: #fff !important;
  border: 0 !important;
  border-bottom: 1px dashed #ced4da !important;
  transition: .2s;
}
.table-input:focus {
  border-bottom: solid 1px #00acc1 !important;
  outline: 0;
}

.textarea {
  font-size: 16px;
}

label { width: 100% !important; padding: 0 !important; }
.flex { display: flex; }
.flex-row { display: flex; margin: 0 auto; padding: 0; }
.col-h { width: 48%; margin: 10px; }
.form-row { display: flex; }
.form-group { margin-bottom: .5rem; }

table {
  border-collapse: collapse;
  margin-bottom: 24px;
  margin-right: 24px;
}
.thead { cursor: default !important; }
th { text-align: center; background: #f5f7f9; }
tr:not(.sum-tr) { cursor: move; }
th, td {
  border: solid 1px #ccc;
  padding: 10px;
  box-sizing: border-box;
}
td:not(.action-cell) { background: #fff; }

summary { outline: 0; position: relative; left: 5px;}
details[open] .details-content {
  animation: fadeIn 0.5s ease;
}
details img { cursor: default; }
@keyframes fadeIn {
    0% { opacity: 0; transform: translateY(-10px); }
  100% { opacity: 1; transform: none; }
}

.readonly {
  background: #f5f7f9 !important;
}

.invisible { visibility: hidden; }
.action-cell { text-align: center; border: none; background-color: transparent; }
.action-cell:hover .delrow-btn { opacity: 1; }
.delrow-btn {
  color: #777;
  background-color: transparent;
  text-decoration: none;
  cursor: pointer;
  transition: .1s;
  opacity: 0;
}
.delrow-btn:hover { color: #aaa; }

.item-cell { position: relative; }
.items_box {
  background: #f5f7f9;
  border: solid 1px #ccc;
	border-radius: .2em;
  box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
	display: none;
  width: 100%;
	height: 180px;
	overflow: scroll;
  position: absolute;
  z-index: 10;
  left: 2px;
}
.items {
	background: #f5f7f9;
  color: #0190a2;
	display: inline-block;
	padding: 0;
	margin: 5px 0;
	list-style: none;
}
.items_name { padding: 4px 10px; cursor: pointer; }
.items_name:hover { background: #DCF0F4; }
</style>

<div id="app">

<form method="post" action="" autocomplete="off">

<div class="row mb-3">
  <div class="col-12">
    <a href="./" class="btn btn-primary"><i class="fa fa-reply"></i> 戻る</a>
    <a class="btn btn-primary" id="add_button"><i class="fa fa-floppy-o"></i> 保存する</a>
  </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-row">
            <div class="form-group">
                <label>取引先 <span class="badge rounded-pill bg-warning text-dark">必須</span></label>
                <select name="destination" class="input" id="customer">
                  <option selected disabled>選択してください</option>
                  <?php //echo $this->Common->makeSelectOptions($clients);?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-10">
              <input type="text" name="responsible" placeholder="担当者" class="input">
            </div>
            <div class="form-group col-2">
            <input type="text" name="honor_title" class="input" value="御中">
            </div>
        </div>

        <div class="form-row">
          <div class="form-group col-6">
            <label>発行日 <span class="badge rounded-pill bg-warning text-dark">必須</span></label>
            <input type="text" id="issued_date" name="issued_date" class="input" value="<?= date('Y/m/d');?>">
          </div>
          <div class="form-group col-6">
            <label>有効期限</label>
            <input type="text" id="exp_date" name="exp_date" class="input">
          </div>
        </div>

        <div class="form-group">
            <label>見積番号 <span class="badge rounded-pill bg-warning text-dark">必須</span></label>
            <input type="text" name="estimate_no" class="input" value="<?= date('Ymd') ?>">
        </div>
        <div class="form-group">
            <label>件名</label>
            <input type="text" name="title" class="input" v-model.trim="titleCount">
            <small>70</small>
        </div>
    </div>
</div>

    <div class="row">
      <table id="table">
        <thead>
          <tr class="thead">
            <th style="width: 3%; visibility: hidden; border: none;"></th>
            <th style="width: 30%;">品番・品名</th>
            <th style="width: 10%;">数量</th>
            <th style="width: 10%;">単位</th>
            <th style="width: 12%;">単価</th>
            <th style="width: 15%;">税区分</th>
            <th style="width: 20%;">金額</th>
          </tr>
        </thead>
        <tbody class="main_tbody" id="sortable">
          <!-- 列 -->
          <tr class="sortable-tr">
            <td class="action-cell"><span class="delrow-btn">×</span></td>
            <td class="item-cell">
              <input type="text" name="item_name[]" class="table-input ti-name">
              <div class="items_box">
                <ul class="items">
                  <?php foreach ($items as $item): ?>
                    <li class="items_name" data-name="<?= $item['Item']['item_name']; ?>" data-unit="<?= $item['Item']['unit']; ?>" data-cost="<?= $item['Item']['cost']; ?>" data-tax="<?= $item['Item']['tax']; ?>"><?= $item['Item']['item_name']; ?> @<?= number_format($item['Item']['cost']); ?>円</li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </td>
            <td><input type="text" name="qty[]" id="qty1" class="table-input ti-qty text-end"></td>
            <td><input type="text" name="unit[]" class="table-input ti-unit text-center" placeholder="単位" value=""></td>
            <td><input type="text" name="cost[]" id="cost1" class="table-input ti-cost text-end" value=""></td>
            <td>
              <select name="tax[]" class="table-input ti-tax">
                <option value="1">10%</option>
                <option value="2">軽減8%</option>
                <option value="3">8%</option>
                <option value="4">対象外</option>
                <option value="5">5%</option>
              </select>
            </td>
            <td><input type="text" class="table-input ti-sum text-end readonly" tabindex="-1" readonly></td>
          </tr>
          <!-- 列 -->
          <tr class="sortable-tr">
            <td class="action-cell"><span class="delrow-btn">×</span></td>
            <td class="item-cell">
              <input type="text" name="item_name[]" class="table-input ti-name">
              <div class="items_box">
                <ul class="items">
                  <?php foreach ($items as $item): ?>
                    <li class="items_name" data-name="<?= $item['Item']['item_name']; ?>" data-unit="<?= $item['Item']['unit']; ?>" data-cost="<?= $item['Item']['cost']; ?>" data-tax="<?= $item['Item']['tax']; ?>"><?= $item['Item']['item_name']; ?> @<?= number_format($item['Item']['cost']); ?>円</li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </td>
            <td><input type="text" name="qty[]" class="table-input ti-qty text-end"></td>
            <td><input type="text" name="unit[]" class="table-input ti-unit text-center" placeholder="単位"></td>
            <td><input type="text" name="cost[]" class="table-input ti-cost text-end"></td>
            <td>
              <select name="tax[]" class="table-input ti-tax">
                <option value="1">10%</option>
                <option value="2">軽減8%</option>
                <option value="3">8%</option>
                <option value="4">対象外</option>
                <option value="5">5%</option>
              </select>
            </td>
            <td><input type="text" class="table-input ti-sum text-end readonly" name="price" tabindex="-1" readonly></td>
          </tr>
        </tbody>

        <!-- 計算結果 -->
        <tbody>
          <tr class="sum-tr">
            <td rowspan="3" style="border: none !important; vertical-align: top;"></td>
            <td colspan="3" rowspan="3" style="border: none !important; vertical-align: top;">
              <span href="#" onclick="addRow()" class="link" id="rowAddBtn"><i class="fa fa-plus"></i> 行の追加<span id="rowRemain"></span></span>
            </td>
            <td colspan="2" style="text-align: center;">小計</td>
            <td><input type="text" id="subtotal" class="table-input text-end readonly" value="0" readonly tabindex="-1" value=""></td>
          </tr>
          <tr class="sum-tr">
            <td colspan="2" style="text-align: center;">消費税</td>
            <td><input type="text" id="taxTotal" class="table-input text-end readonly" value="0" readonly tabindex="-1" value=""></td>
          </tr>
          <tr class="sum-tr">
            <td colspan="2" style="text-align: center;">合計</td>
            <td><input type="text" name="price" id="totalPrice" class="table-input text-end readonly" value="0" readonly tabindex="-1"></td>
          </tr>
        </tbody>
      </table>

      <label>備考</label>
      <textarea class="input textarea" name="remarks" id="remarks" v-model.trim="remarksCount" style="height: 74px;"></textarea>
      <small>1000</small>
      <span href="#" v-on:click="fillRemarks" class="link" style="display: block;">自社情報の備考を使う</span>
    </div>

    <!-- 登録情報 -->
    <input type="hidden" name="reg_uid" value="<?= ''?>">
    <input type="hidden" name="reg_datetime" value="<?= date('Y-m-d H:i:s');?>">
  </form>

</div><!--app-->

@endsection
