<?php
$url_self     = $this->Html->url("/".$self_dir, true);
$url_add      = $url_self."/add";

$months = array();
for ($i=1; $i <= 12; $i++) {
	$val = $i;
	$months[$val] = str_pad($val, 2, "0", STR_PAD_LEFT);
}
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

<a href="<?= $url_self;?>" class="btn btn-primary"><i class="fa fa-reply"></i> 戻る</a>
<a class="btn btn-primary" id="add_button"><i class="fa fa-floppy-o"></i> 保存する</a>

<div id="successMessage" class="message">
  <p><i class="fa fa-thumbs-up"></i> 請求書に変換されました。</p>
</div>

    <div class="row flex-row">
        <div class="col-h">
            <p class="title">請求情報</p>
            <div class="form-row">
                <div class="form-group">
                    <label>取引先 <span class="required-badge">必須</span></label>
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
                    <select name="honor_title" id="" class="input">
                      <option value="御中">御中</option>
                      <option value="様">様</option>
                      <option value="">(空白)</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
              <div class="form-group col-6">
                  <label>請求日 <span class="required-badge">必須</span></label>
                  <input type="text" id="issued_date" name="issued_date" class="input" value="<?= date('Y/m/d');?>">
              </div>
              <div class="form-group col-6">
                <label>お支払い期限</label>
                <input type="text" id="exp_date" name="exp_date" class="input">
              </div>
            </div>

            <div class="form-group">
                <label>請求番号 <span class="required-badge">必須</span></label>
                <input type="text" name="estimate_no" class="input" value="<?= date('Ymd').'-'.$repeatNum;?>">
            </div>
            <div class="form-group">
                <label>件名</label>
                <input type="text" name="title" class="input" v-model.trim="titleCount">
                <small>{{ titleCount.length }} / 70</small>
            </div>
        </div>
        <div class="col-h">
          <p class="title">請求元情報</p>
          <p><?= $companyData['company_name'];?></p>
          <p>〒<?= $companyData['zipcode'];?></p>
          <p><?= $companyData['address1'];?></p>
          <p><?= $companyData['address2'];?></p>
          <p><?= $companyData['address3'];?></p>
          <p>TEL:<?= $companyData['tel_no'];?> | FAX:<?= $companyData['fax_no'];?></p>
          <label>
            消費税設定：
            <?php
            switch($companyData['tax_cfg']) {
              case 0: echo '税別表示'; break;
              case 1: echo '税込表示'; break;
              case 2: echo '税込表示（免税）'; break;
            };
            echo ', ';
            switch($companyData['tax_round']) {
              case 0: echo '切り捨て'; break;
              case 1: echo '切り上げ'; break;
              case 2: echo '四捨五入'; break;
            };
            ?>
          </label>
          <a href="../../company" target="_blank" class="link" tabindex="-1">自社情報を変更する</a>
        </div>
    </div>

    <div class="row">
      <table class="table" id="table">
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
          <?php foreach($details as $detail): ?>
          <tr class="sortable-tr">
            <td class="action-cell"><span class="delrow-btn">×</span></td>
            <td class="item-cell">
              <input type="text" name="item_name[]" class="table-input ti-name" value="<?= $detail['EstimateDetail']['item_name'];?>">
              <div class="items_box">
                <ul class="items">
                  <?php foreach ($items as $item): ?>
                    <li class="items_name" data-name="<?= $item['Item']['item_name']; ?>" data-unit="<?= $item['Item']['unit']; ?>" data-cost="<?= $item['Item']['cost']; ?>" data-tax="<?= $item['Item']['tax']; ?>"><?= $item['Item']['item_name']; ?> @<?= number_format($item['Item']['cost']); ?>円</li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </td>
            <td><input type="text" name="qty[]" id="qty1" class="table-input ti-qty text-end" value="<?= $detail['EstimateDetail']['qty'];?>"></td>
            <td><input type="text" name="unit[]" class="table-input ti-unit text-center" placeholder="単位"  value="<?= $detail['EstimateDetail']['unit'];?>"></td>
            <td><input type="text" name="cost[]" id="cost1" class="table-input ti-cost text-end" value="<?= $detail['EstimateDetail']['cost'];?>"></td>
            <td>
            <?php
                switch($detail['EstimateDetail']['tax_id']) {
                  case 1: $selected1 = 'selected'; $selected2 = ''; $selected3 = ''; $selected4 = ''; $selected5 = ''; break;
                  case 2: $selected2 = 'selected'; $selected1 = ''; $selected3 = ''; $selected4 = ''; $selected5 = ''; break;
                  case 3: $selected3 = 'selected'; $selected1 = ''; $selected2 = ''; $selected4 = ''; $selected5 = ''; break;
                  case 4: $selected4 = 'selected'; $selected1 = ''; $selected2 = ''; $selected3 = ''; $selected5 = ''; break;
                  case 5: $selected5 = 'selected'; $selected1 = ''; $selected2 = ''; $selected3 = ''; $selected4 = ''; break;
                }
            ?>
              <select name="tax[]" class="table-input ti-tax">
                <option value="1" <?= $selected1;?>>10%</option>
                <option value="2" <?= $selected2;?>>軽減8%</option>
                <option value="3" <?= $selected3;?>>8%</option>
                <option value="4" <?= $selected4;?>>対象外</option>
                <option value="5" <?= $selected5;?>>5%</option>
              </select>
            </td>
            <td><input type="text" class="table-input ti-sum text-end readonly" tabindex="-1" readonly></td>
            <input type="hidden" name="id[]" value="<?= $detail['EstimateDetail']['id'];?>">
          </tr>
          <?php endforeach; ?>
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
      <small>{{ remarksCount.length }} / 1000</small>
      <span href="#" v-on:click="fillRemarks" class="link" style="display: block;">自社情報の備考を使う</span>
    </div>

    <!-- 登録情報 -->
    <input type="hidden" name="reg_uid" value="<?= $uid;?>">
    <input type="hidden" name="reg_datetime" value="<?= date('Y-m-d H:i:s');?>">
  </form>

</div><!--app-->

<script>
window.onload = function() {
  addUniqueId();
  $('#customer').val('<?= $header['Estimate']['destination'];?>');
}
/* 列を追加 */
function addRow() {

  let lines = $('.main_tbody').children().length;

  // 追加上限30
  if (lines < 30) {

    let rowData  = '<tr class="sortable-tr">';
        rowData += '<td class="action-cell"><span class="delrow-btn">×</span></td>';

        rowData += '<td class="item-cell">';
        rowData += '<input type="text" name="item_name[]" class="table-input ti-name" list="item_list">';
        rowData += '<div class="items_box">';
        rowData += '<ul class="items">';
        <?php foreach ($items as $item): ?>
        <?php //for($): ?>
        rowData += '<li class="items_name" id="<?= 'item_name'?>" data-name="<?= $item['Item']['item_name']; ?>" data-unit="<?= $item['Item']['unit']; ?>" data-cost="<?= $item['Item']['cost']; ?>" data-tax="<?= $item['Item']['tax']; ?>"><?= $item['Item']['item_name']; ?> @<?= number_format($item['Item']['cost']); ?>円</li>';
        <?php //endfor; ?>
        <?php endforeach; ?>
        rowData += '</ul>';
        rowData += '</div>';
        rowData += '</td>';
        rowData += '<td><input type="text" name="qty[]" class="table-input ti-qty text-end"></td>'
        rowData += '<td><input type="text" name="unit[]" class="table-input ti-unit text-center" placeholder="単位"></td>';
        rowData += '<td><input type="text" name="cost[]" class="table-input ti-cost text-end"></td>';
        rowData += '<td>';
        rowData += '  <select name="tax[]" class="table-input ti-tax">';
        rowData += '    <option value="1" selected>10%</option>';
        rowData += '    <option value="2">軽減8%</option>';
        rowData += '    <option value="3">8%</option>';
        rowData += '    <option value="4">対象外</option>';
        rowData += '    <option value="5">5%</option>';
        rowData += '  </select>';
        rowData += '</td>';
        rowData += '<td><input type="text" class="table-input ti-sum text-end readonly" tabindex="-1" readonly value=""></td>';
        rowData += '</tr>';

    $(function() {
      $('.main_tbody').append(rowData);
      addUniqueId();
    });

    rowRemain.innerHTML = '(残り' + (29 - lines) + '行)';
  }

}

// カンマ除去
function removeComma(number) {
  let removed = number.replace(/,/g, '');
  return parseInt(removed, 10);
}

// 品目リスト表示
function showItemList(nameId) {
  setTimeout(function() {
    $('#ib' + nameId).css('display','inline-block');
  }, 500);
  $(document).on('blur', '#name' + nameId, function(){
    $('#ib' + nameId).css('display','none');
  });
}

// 明細入力時にクリックorフォーカスした要素idの連番部分を取得してshowItemListに渡す
$(document).on('click focus', '.table-input', function() {
  let nameId = ($(':focus').attr('id')).replace('name', '');
  showItemList(nameId);
});

$(function() {

  // 品目を選択したら自動入力
  $(document).on('click', '.items_name', function(){
    let nameData  = $(this).data('name');
    let unitData  = $(this).data('unit');
    let costData  = $(this).data('cost');
    let taxData   = $(this).data('tax');
    let focusId   = ($(':focus').attr('id')).replace('name', '');
    $('#name' + focusId).val(nameData);
    $('#qty'  + focusId).val(1);
    $('#unit' + focusId).val(unitData);
    $('#cost' + focusId).val(costData);
    $('#tax'  + focusId).val(taxData);
    $('.table-input').change(); // 計算処理onchange発火用
    $('.items_box').css('display','none');
    $('#name' + focusId).blur();
  });

  // フォーム送信
  $('#add_button').on('click', function(event) {
      $('form').submit();
  });

  // デートピッカー
  $('#issued_date').datepicker({ dateFormat:'yy/mm/dd', showButtonPanel: true });
  $('#exp_date').datepicker({ dateFormat:'yy/mm/dd', showButtonPanel: true });

  // 列を削除
  $('table').on('click', '.delrow-btn', function() {
      let row = $(this).closest('tr');
      $(row).remove();
      lines = $('.main_tbody').children().length;
      lines -= 1;
      rowRemain.innerHTML = '(残り' + (29 - lines) + '行)';
      $('.table-input').change(); // 計算処理onchange発火用
  });

  // セルの横幅を固定したままSortable
  function fixPlaceHolderWidth(event, ui){
    ui.children().each(function() {
        $(this).width($(this).width());
    });
    return ui;
  };
  $('#sortable').sortable({
      'items': 'tr.sortable-tr',
      start: function(event, ui) {
          ui.placeholder.height(ui.helper.outerHeight());
      },
      helper: fixPlaceHolderWidth,
      update: function(event, ui) {
        console.log($('#sortable').sortable('toArray'));
      },
  });

});

// 連番id割り当て
function addUniqueId() {
  $('input.ti-name').each(function(i) {
      $(this).attr('id', 'name' + (i+1));
  });
  $('.items_box').each(function(i) {
      $(this).attr('id', 'ib' + (i+1));
  });
  $('input.ti-qty').each(function(i) {
      $(this).attr('id', 'qty' + (i+1));
  });
  $('input.ti-unit').each(function(i) {
      $(this).attr('id', 'unit' + (i+1));
  });
  $('input.ti-cost').each(function(i) {
      $(this).attr('id', 'cost' + (i+1));
  });
  $('select.ti-tax').each(function(i) {
      $(this).attr('id', 'tax' + (i+1));
  });
  $('input.ti-sum').each(function(i) {
      $(this).attr('id', 'sum' + (i+1));
  });
}

// 計算
$(window).on('load', function() {
  $('.table-input').change(); // 計算処理onchange発火用
});
$(document).on('input change', '.table-input', function() {

  let taxRate1;
  let taxRate2;
  let taxRate3;
  let taxRate4;
  let taxRate5;
  let taxRate6;
  let taxRate7;
  let taxRate8;
  let taxRate9;
  let taxRate10;
  let taxRate11;
  let taxRate12;
  let taxRate13;
  let taxRate14;
  let taxRate15;
  let taxRate16;
  let taxRate17;
  let taxRate18;
  let taxRate19;
  let taxRate20;
  let taxRate21;
  let taxRate22;
  let taxRate23;
  let taxRate24;
  let taxRate25;
  let taxRate26;
  let taxRate27;
  let taxRate28;
  let taxRate29;
  let taxRate30;

  let subtotalArray = [];
  let taxArray = [];

  /* --- Python 自動生成コード --- */
  if (typeof qty1 !== 'undefined') {
    switch(Number(tax1.value)) {
      case 1: taxRate1 = 0.1;  break;
      case 2: taxRate1 = 0.08; break;
      case 3: taxRate1 = 0.08; break;
      case 4: taxRate1 = 0;    break;
      case 5: taxRate1 = 0.05; break;
      default: break;
    }
    if (qty1.value !== '' && cost1.value !== '') {
      sum1.value = (qty1.value * cost1.value).toLocaleString();
      subtotalArray.splice(0, 1, removeComma(sum1.value));
      taxArray.splice(0, 1, taxRate1);
    }  if (qty1.value == '' || cost1.value == '') {
      sum1.value = '';
    }
  }

  if (typeof qty2 !== 'undefined') {
    switch(Number(tax2.value)) {
      case 1: taxRate2 = 0.1;  break;
      case 2: taxRate2 = 0.08; break;
      case 3: taxRate2 = 0.08; break;
      case 4: taxRate2 = 0;    break;
      case 5: taxRate2 = 0.05; break;
      default: break;
    }
    if (qty2.value !== '' && cost2.value !== '') {
      sum2.value = (qty2.value * cost2.value).toLocaleString();
      subtotalArray.splice(1, 1, removeComma(sum2.value));
      taxArray.splice(1, 1, taxRate2);
    }  if (qty2.value == '' || cost2.value == '') {
      sum2.value = '';
    }
  }

  if (typeof qty3 !== 'undefined') {
    switch(Number(tax3.value)) {
      case 1: taxRate3 = 0.1;  break;
      case 2: taxRate3 = 0.08; break;
      case 3: taxRate3 = 0.08; break;
      case 4: taxRate3 = 0;    break;
      case 5: taxRate3 = 0.05; break;
      default: break;
    }
    if (qty3.value !== '' && cost3.value !== '') {
      sum3.value = (qty3.value * cost3.value).toLocaleString();
      subtotalArray.splice(2, 1, removeComma(sum3.value));
      taxArray.splice(2, 1, taxRate3);
    }  if (qty3.value == '' || cost3.value == '') {
      sum3.value = '';
    }
  }

  if (typeof qty4 !== 'undefined') {
    switch(Number(tax4.value)) {
      case 1: taxRate4 = 0.1;  break;
      case 2: taxRate4 = 0.08; break;
      case 3: taxRate4 = 0.08; break;
      case 4: taxRate4 = 0;    break;
      case 5: taxRate4 = 0.05; break;
      default: break;
    }
    if (qty4.value !== '' && cost4.value !== '') {
      sum4.value = (qty4.value * cost4.value).toLocaleString();
      subtotalArray.splice(3, 1, removeComma(sum4.value));
      taxArray.splice(3, 1, taxRate4);
    }  if (qty4.value == '' || cost4.value == '') {
      sum4.value = '';
    }
  }

  if (typeof qty5 !== 'undefined') {
    switch(Number(tax5.value)) {
      case 1: taxRate5 = 0.1;  break;
      case 2: taxRate5 = 0.08; break;
      case 3: taxRate5 = 0.08; break;
      case 4: taxRate5 = 0;    break;
      case 5: taxRate5 = 0.05; break;
      default: break;
    }
    if (qty5.value !== '' && cost5.value !== '') {
      sum5.value = (qty5.value * cost5.value).toLocaleString();
      subtotalArray.splice(4, 1, removeComma(sum5.value));
      taxArray.splice(4, 1, taxRate5);
    }  if (qty5.value == '' || cost5.value == '') {
      sum5.value = '';
    }
  }

  if (typeof qty6 !== 'undefined') {
    switch(Number(tax6.value)) {
      case 1: taxRate6 = 0.1;  break;
      case 2: taxRate6 = 0.08; break;
      case 3: taxRate6 = 0.08; break;
      case 4: taxRate6 = 0;    break;
      case 5: taxRate6 = 0.05; break;
      default: break;
    }
    if (qty6.value !== '' && cost6.value !== '') {
      sum6.value = (qty6.value * cost6.value).toLocaleString();
      subtotalArray.splice(5, 1, removeComma(sum6.value));
      taxArray.splice(5, 1, taxRate6);
    }  if (qty6.value == '' || cost6.value == '') {
      sum6.value = '';
    }
  }

  if (typeof qty7 !== 'undefined') {
    switch(Number(tax7.value)) {
      case 1: taxRate7 = 0.1;  break;
      case 2: taxRate7 = 0.08; break;
      case 3: taxRate7 = 0.08; break;
      case 4: taxRate7 = 0;    break;
      case 5: taxRate7 = 0.05; break;
      default: break;
    }
    if (qty7.value !== '' && cost7.value !== '') {
      sum7.value = (qty7.value * cost7.value).toLocaleString();
      subtotalArray.splice(6, 1, removeComma(sum7.value));
      taxArray.splice(6, 1, taxRate7);
    }  if (qty7.value == '' || cost7.value == '') {
      sum7.value = '';
    }
  }

  if (typeof qty8 !== 'undefined') {
    switch(Number(tax8.value)) {
      case 1: taxRate8 = 0.1;  break;
      case 2: taxRate8 = 0.08; break;
      case 3: taxRate8 = 0.08; break;
      case 4: taxRate8 = 0;    break;
      case 5: taxRate8 = 0.05; break;
      default: break;
    }
    if (qty8.value !== '' && cost8.value !== '') {
      sum8.value = (qty8.value * cost8.value).toLocaleString();
      subtotalArray.splice(7, 1, removeComma(sum8.value));
      taxArray.splice(7, 1, taxRate8);
    }  if (qty8.value == '' || cost8.value == '') {
      sum8.value = '';
    }
  }

  if (typeof qty9 !== 'undefined') {
    switch(Number(tax9.value)) {
      case 1: taxRate9 = 0.1;  break;
      case 2: taxRate9 = 0.08; break;
      case 3: taxRate9 = 0.08; break;
      case 4: taxRate9 = 0;    break;
      case 5: taxRate9 = 0.05; break;
      default: break;
    }
    if (qty9.value !== '' && cost9.value !== '') {
      sum9.value = (qty9.value * cost9.value).toLocaleString();
      subtotalArray.splice(8, 1, removeComma(sum9.value));
      taxArray.splice(8, 1, taxRate9);
    }  if (qty9.value == '' || cost9.value == '') {
      sum9.value = '';
    }
  }

  if (typeof qty10 !== 'undefined') {
    switch(Number(tax10.value)) {
      case 1: taxRate10 = 0.1;  break;
      case 2: taxRate10 = 0.08; break;
      case 3: taxRate10 = 0.08; break;
      case 4: taxRate10 = 0;    break;
      case 5: taxRate10 = 0.05; break;
      default: break;
    }
    if (qty10.value !== '' && cost10.value !== '') {
      sum10.value = (qty10.value * cost10.value).toLocaleString();
      subtotalArray.splice(9, 1, removeComma(sum10.value));
      taxArray.splice(9, 1, taxRate10);
    }  if (qty10.value == '' || cost10.value == '') {
      sum10.value = '';
    }
  }

  if (typeof qty11 !== 'undefined') {
    switch(Number(tax11.value)) {
      case 1: taxRate11 = 0.1;  break;
      case 2: taxRate11 = 0.08; break;
      case 3: taxRate11 = 0.08; break;
      case 4: taxRate11 = 0;    break;
      case 5: taxRate11 = 0.05; break;
      default: break;
    }
    if (qty11.value !== '' && cost11.value !== '') {
      sum11.value = (qty11.value * cost11.value).toLocaleString();
      subtotalArray.splice(10, 1, removeComma(sum11.value));
      taxArray.splice(10, 1, taxRate11);
    }  if (qty11.value == '' || cost11.value == '') {
      sum11.value = '';
    }
  }

  if (typeof qty12 !== 'undefined') {
    switch(Number(tax12.value)) {
      case 1: taxRate12 = 0.1;  break;
      case 2: taxRate12 = 0.08; break;
      case 3: taxRate12 = 0.08; break;
      case 4: taxRate12 = 0;    break;
      case 5: taxRate12 = 0.05; break;
      default: break;
    }
    if (qty12.value !== '' && cost12.value !== '') {
      sum12.value = (qty12.value * cost12.value).toLocaleString();
      subtotalArray.splice(11, 1, removeComma(sum12.value));
      taxArray.splice(11, 1, taxRate12);
    }  if (qty12.value == '' || cost12.value == '') {
      sum12.value = '';
    }
  }

  if (typeof qty13 !== 'undefined') {
    switch(Number(tax13.value)) {
      case 1: taxRate13 = 0.1;  break;
      case 2: taxRate13 = 0.08; break;
      case 3: taxRate13 = 0.08; break;
      case 4: taxRate13 = 0;    break;
      case 5: taxRate13 = 0.05; break;
      default: break;
    }
    if (qty13.value !== '' && cost13.value !== '') {
      sum13.value = (qty13.value * cost13.value).toLocaleString();
      subtotalArray.splice(12, 1, removeComma(sum13.value));
      taxArray.splice(12, 1, taxRate13);
    }  if (qty13.value == '' || cost13.value == '') {
      sum13.value = '';
    }
  }

  if (typeof qty14 !== 'undefined') {
    switch(Number(tax14.value)) {
      case 1: taxRate14 = 0.1;  break;
      case 2: taxRate14 = 0.08; break;
      case 3: taxRate14 = 0.08; break;
      case 4: taxRate14 = 0;    break;
      case 5: taxRate14 = 0.05; break;
      default: break;
    }
    if (qty14.value !== '' && cost14.value !== '') {
      sum14.value = (qty14.value * cost14.value).toLocaleString();
      subtotalArray.splice(13, 1, removeComma(sum14.value));
      taxArray.splice(13, 1, taxRate14);
    }  if (qty14.value == '' || cost14.value == '') {
      sum14.value = '';
    }
  }

  if (typeof qty15 !== 'undefined') {
    switch(Number(tax15.value)) {
      case 1: taxRate15 = 0.1;  break;
      case 2: taxRate15 = 0.08; break;
      case 3: taxRate15 = 0.08; break;
      case 4: taxRate15 = 0;    break;
      case 5: taxRate15 = 0.05; break;
      default: break;
    }
    if (qty15.value !== '' && cost15.value !== '') {
      sum15.value = (qty15.value * cost15.value).toLocaleString();
      subtotalArray.splice(14, 1, removeComma(sum15.value));
      taxArray.splice(14, 1, taxRate15);
    }  if (qty15.value == '' || cost15.value == '') {
      sum15.value = '';
    }
  }

  if (typeof qty16 !== 'undefined') {
    switch(Number(tax16.value)) {
      case 1: taxRate16 = 0.1;  break;
      case 2: taxRate16 = 0.08; break;
      case 3: taxRate16 = 0.08; break;
      case 4: taxRate16 = 0;    break;
      case 5: taxRate16 = 0.05; break;
      default: break;
    }
    if (qty16.value !== '' && cost16.value !== '') {
      sum16.value = (qty16.value * cost16.value).toLocaleString();
      subtotalArray.splice(15, 1, removeComma(sum16.value));
      taxArray.splice(15, 1, taxRate16);
    }  if (qty16.value == '' || cost16.value == '') {
      sum16.value = '';
    }
  }

  if (typeof qty17 !== 'undefined') {
    switch(Number(tax17.value)) {
      case 1: taxRate17 = 0.1;  break;
      case 2: taxRate17 = 0.08; break;
      case 3: taxRate17 = 0.08; break;
      case 4: taxRate17 = 0;    break;
      case 5: taxRate17 = 0.05; break;
      default: break;
    }
    if (qty17.value !== '' && cost17.value !== '') {
      sum17.value = (qty17.value * cost17.value).toLocaleString();
      subtotalArray.splice(16, 1, removeComma(sum17.value));
      taxArray.splice(16, 1, taxRate17);
    }  if (qty17.value == '' || cost17.value == '') {
      sum17.value = '';
    }
  }

  if (typeof qty18 !== 'undefined') {
    switch(Number(tax18.value)) {
      case 1: taxRate18 = 0.1;  break;
      case 2: taxRate18 = 0.08; break;
      case 3: taxRate18 = 0.08; break;
      case 4: taxRate18 = 0;    break;
      case 5: taxRate18 = 0.05; break;
      default: break;
    }
    if (qty18.value !== '' && cost18.value !== '') {
      sum18.value = (qty18.value * cost18.value).toLocaleString();
      subtotalArray.splice(17, 1, removeComma(sum18.value));
      taxArray.splice(17, 1, taxRate18);
    }  if (qty18.value == '' || cost18.value == '') {
      sum18.value = '';
    }
  }

  if (typeof qty19 !== 'undefined') {
    switch(Number(tax19.value)) {
      case 1: taxRate19 = 0.1;  break;
      case 2: taxRate19 = 0.08; break;
      case 3: taxRate19 = 0.08; break;
      case 4: taxRate19 = 0;    break;
      case 5: taxRate19 = 0.05; break;
      default: break;
    }
    if (qty19.value !== '' && cost19.value !== '') {
      sum19.value = (qty19.value * cost19.value).toLocaleString();
      subtotalArray.splice(18, 1, removeComma(sum19.value));
      taxArray.splice(18, 1, taxRate19);
    }  if (qty19.value == '' || cost19.value == '') {
      sum19.value = '';
    }
  }

  if (typeof qty20 !== 'undefined') {
    switch(Number(tax20.value)) {
      case 1: taxRate20 = 0.1;  break;
      case 2: taxRate20 = 0.08; break;
      case 3: taxRate20 = 0.08; break;
      case 4: taxRate20 = 0;    break;
      case 5: taxRate20 = 0.05; break;
      default: break;
    }
    if (qty20.value !== '' && cost20.value !== '') {
      sum20.value = (qty20.value * cost20.value).toLocaleString();
      subtotalArray.splice(19, 1, removeComma(sum20.value));
      taxArray.splice(19, 1, taxRate20);
    }  if (qty20.value == '' || cost20.value == '') {
      sum20.value = '';
    }
  }

  if (typeof qty21 !== 'undefined') {
    switch(Number(tax21.value)) {
      case 1: taxRate21 = 0.1;  break;
      case 2: taxRate21 = 0.08; break;
      case 3: taxRate21 = 0.08; break;
      case 4: taxRate21 = 0;    break;
      case 5: taxRate21 = 0.05; break;
      default: break;
    }
    if (qty21.value !== '' && cost21.value !== '') {
      sum21.value = (qty21.value * cost21.value).toLocaleString();
      subtotalArray.splice(20, 1, removeComma(sum21.value));
      taxArray.splice(20, 1, taxRate21);
    }  if (qty21.value == '' || cost21.value == '') {
      sum21.value = '';
    }
  }

  if (typeof qty22 !== 'undefined') {
    switch(Number(tax22.value)) {
      case 1: taxRate22 = 0.1;  break;
      case 2: taxRate22 = 0.08; break;
      case 3: taxRate22 = 0.08; break;
      case 4: taxRate22 = 0;    break;
      case 5: taxRate22 = 0.05; break;
      default: break;
    }
    if (qty22.value !== '' && cost22.value !== '') {
      sum22.value = (qty22.value * cost22.value).toLocaleString();
      subtotalArray.splice(21, 1, removeComma(sum22.value));
      taxArray.splice(21, 1, taxRate22);
    }  if (qty22.value == '' || cost22.value == '') {
      sum22.value = '';
    }
  }

  if (typeof qty23 !== 'undefined') {
    switch(Number(tax23.value)) {
      case 1: taxRate23 = 0.1;  break;
      case 2: taxRate23 = 0.08; break;
      case 3: taxRate23 = 0.08; break;
      case 4: taxRate23 = 0;    break;
      case 5: taxRate23 = 0.05; break;
      default: break;
    }
    if (qty23.value !== '' && cost23.value !== '') {
      sum23.value = (qty23.value * cost23.value).toLocaleString();
      subtotalArray.splice(22, 1, removeComma(sum23.value));
      taxArray.splice(22, 1, taxRate23);
    }  if (qty23.value == '' || cost23.value == '') {
      sum23.value = '';
    }
  }

  if (typeof qty24 !== 'undefined') {
    switch(Number(tax24.value)) {
      case 1: taxRate24 = 0.1;  break;
      case 2: taxRate24 = 0.08; break;
      case 3: taxRate24 = 0.08; break;
      case 4: taxRate24 = 0;    break;
      case 5: taxRate24 = 0.05; break;
      default: break;
    }
    if (qty24.value !== '' && cost24.value !== '') {
      sum24.value = (qty24.value * cost24.value).toLocaleString();
      subtotalArray.splice(23, 1, removeComma(sum24.value));
      taxArray.splice(23, 1, taxRate24);
    }  if (qty24.value == '' || cost24.value == '') {
      sum24.value = '';
    }
  }

  if (typeof qty25 !== 'undefined') {
    switch(Number(tax25.value)) {
      case 1: taxRate25 = 0.1;  break;
      case 2: taxRate25 = 0.08; break;
      case 3: taxRate25 = 0.08; break;
      case 4: taxRate25 = 0;    break;
      case 5: taxRate25 = 0.05; break;
      default: break;
    }
    if (qty25.value !== '' && cost25.value !== '') {
      sum25.value = (qty25.value * cost25.value).toLocaleString();
      subtotalArray.splice(24, 1, removeComma(sum25.value));
      taxArray.splice(24, 1, taxRate25);
    }  if (qty25.value == '' || cost25.value == '') {
      sum25.value = '';
    }
  }

  if (typeof qty26 !== 'undefined') {
    switch(Number(tax26.value)) {
      case 1: taxRate26 = 0.1;  break;
      case 2: taxRate26 = 0.08; break;
      case 3: taxRate26 = 0.08; break;
      case 4: taxRate26 = 0;    break;
      case 5: taxRate26 = 0.05; break;
      default: break;
    }
    if (qty26.value !== '' && cost26.value !== '') {
      sum26.value = (qty26.value * cost26.value).toLocaleString();
      subtotalArray.splice(25, 1, removeComma(sum26.value));
      taxArray.splice(25, 1, taxRate26);
    }  if (qty26.value == '' || cost26.value == '') {
      sum26.value = '';
    }
  }

  if (typeof qty27 !== 'undefined') {
    switch(Number(tax27.value)) {
      case 1: taxRate27 = 0.1;  break;
      case 2: taxRate27 = 0.08; break;
      case 3: taxRate27 = 0.08; break;
      case 4: taxRate27 = 0;    break;
      case 5: taxRate27 = 0.05; break;
      default: break;
    }
    if (qty27.value !== '' && cost27.value !== '') {
      sum27.value = (qty27.value * cost27.value).toLocaleString();
      subtotalArray.splice(26, 1, removeComma(sum27.value));
      taxArray.splice(26, 1, taxRate27);
    }  if (qty27.value == '' || cost27.value == '') {
      sum27.value = '';
    }
  }

  if (typeof qty28 !== 'undefined') {
    switch(Number(tax28.value)) {
      case 1: taxRate28 = 0.1;  break;
      case 2: taxRate28 = 0.08; break;
      case 3: taxRate28 = 0.08; break;
      case 4: taxRate28 = 0;    break;
      case 5: taxRate28 = 0.05; break;
      default: break;
    }
    if (qty28.value !== '' && cost28.value !== '') {
      sum28.value = (qty28.value * cost28.value).toLocaleString();
      subtotalArray.splice(27, 1, removeComma(sum28.value));
      taxArray.splice(27, 1, taxRate28);
    }  if (qty28.value == '' || cost28.value == '') {
      sum28.value = '';
    }
  }

  if (typeof qty29 !== 'undefined') {
    switch(Number(tax29.value)) {
      case 1: taxRate29 = 0.1;  break;
      case 2: taxRate29 = 0.08; break;
      case 3: taxRate29 = 0.08; break;
      case 4: taxRate29 = 0;    break;
      case 5: taxRate29 = 0.05; break;
      default: break;
    }
    if (qty29.value !== '' && cost29.value !== '') {
      sum29.value = (qty29.value * cost29.value).toLocaleString();
      subtotalArray.splice(28, 1, removeComma(sum29.value));
      taxArray.splice(28, 1, taxRate29);
    }  if (qty29.value == '' || cost29.value == '') {
      sum29.value = '';
    }
  }

  if (typeof qty30 !== 'undefined') {
    switch(Number(tax30.value)) {
      case 1: taxRate30 = 0.1;  break;
      case 2: taxRate30 = 0.08; break;
      case 3: taxRate30 = 0.08; break;
      case 4: taxRate30 = 0;    break;
      case 5: taxRate30 = 0.05; break;
      default: break;
    }
    if (qty30.value !== '' && cost30.value !== '') {
      sum30.value = (qty30.value * cost30.value).toLocaleString();
      subtotalArray.splice(29, 1, removeComma(sum30.value));
      taxArray.splice(29, 1, taxRate30);
    }  if (qty30.value == '' || cost30.value == '') {
      sum30.value = '';
    }
  }
  /* --- Python 自動生成コード ここまで --- */

  subtotal.value = sumSubtotalArray(subtotalArray).toLocaleString();
  taxTotal.value = sumTaxArray(subtotalArray, taxArray).toLocaleString();

  // 小計を計算
  function sumSubtotalArray(array) {
    let sum = 0;
    for (let i = 0, len = array.length; i < len; i++) {
      sum += Number(array[i]);
    }
    return sum;
  }

  // 消費税を計算
  function sumTaxArray(subtotal, tax) {
    let sum = 0;
    let taxRound =  <?php switch($companyData['tax_round']) {
                            case 0: echo 0; break;
                            case 1: echo 1; break;
                            case 2: echo 2; break;
                          };
                    ?>;
    for (let i = 0, len = subtotal.length; i < len; i++) {
      sum += Number(subtotal[i]) * Number(tax[i]);
    }
    switch (taxRound) {
      case 0: return Math.floor(sum); break;
      case 1: return Math.ceil(sum);  break;
      case 2: return Math.round(sum); break;
      default: break;
    }
  }

  // 合計を計算
  totalPrice.value = (Number(removeComma(subtotal.value)) + Number(removeComma(taxTotal.value))).toLocaleString();

});

/* Vue.js */
let app = new Vue({
  el: '#app',
  data: {
    titleCount: '',
    remarksCount: '',
  },
  methods: {
    fillRemarks: function() {
      remarks.value = '<?= $companyData['invoice_remarks'];?>';
    }
  }
})
</script>
