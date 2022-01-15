@extends('layouts/default')
@section('page')

<?php
$url_self     = "/";
$url_add      = $url_self."/add";

$months = array();
for ($i=1; $i <= 12; $i++) {
	$val = $i;
	$months[$val] = str_pad($val, 2, "0", STR_PAD_LEFT);
}
?>
<style>
.w-50 { width: 50% !important; }

.text-center { text-align: center; }
.text-right { text-align: right; }

.mb-1 { margin-bottom: 5px; }
.mb-3 { margin-bottom: 1em; }

.title { border-bottom: solid 1px #ccc; font-size: 120%; }
.required-badge {
  background: linear-gradient(to bottom, rgb(250, 177, 26) 0px, rgb(230, 155, 23) 100%) repeat scroll 0% 0% transparent;
  padding: 0 .2em;
  border-radius: 2px;
  color: #fff;
}
.link { color: #00acc1; text-decoration: none; cursor: pointer; }

.container { margin-bottom: 40px; }

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

label:not(.price) { width: 100% !important; padding: 0 !important; display: block; }
.row label { float: none; }
.flex { display: flex; }
.flex-row { display: flex; margin: 0 auto; padding: 0; }
.col-h { width: 48%; margin: 10px; }
.form-row { display: flex; }
.form-group { margin-bottom: .5rem; }

</style>

<form method="post" action="" autocomplete="off" id="MainForm">
@csrf
<a href="<?= $url_self;?>" class="btn btn-primary"><i class="fa fa-reply"></i> 戻る</a>
<a class="btn btn-primary" onclick="MainForm.submit()"><i class="fa fa-floppy-o"></i> 保存する</a>

    <div class="row">
      <p class="title">品目の新規登録</p>
      <div class="col-h">
          <input type="hidden" name="id" value="<?= $item['id'];?>">
        <div class="form-group">
          <label>品番・品名 <span class="required-badge">必須</span></label>
          <input type="text" name="name" class="input mb-1" value="<?= $item['name'];?>">
        </div>
        <div class="form-group">
          <label>単位</label>
          <input type="text" name="unit" class="input w-50 mb-1" value="<?= $item['unit'];?>" list="unit_list">
          <datalist id="unit_list">
            <option value="個"></option>
            <option value="台"></option>
            <option value="本"></option>
            <option value="枚"></option>
            <option value="体"></option>
          </datalist>
        </div>
        <div class="form-group">
          <label>単価</label>
          <div class="flex">
            <input type="text" name="cost" class="input w-50" value="<?= $item['cost'];?>"><label class="price">円</label>
          </div>
        </div>
        <div class="form-group">
          <label>税率</label>
          <select class="input w-50" name="tax">
            <option value="0" <?= $item['tax'] === 0 ? 'selected' : '';?> disabled>選択してください</option>
            <option value="1" <?= $item['tax'] === 1 ? 'selected' : '';?>>10%</option>
            <option value="2" <?= $item['tax'] === 2 ? 'selected' : '';?>>軽減8%</option>
            <option value="3" <?= $item['tax'] === 3 ? 'selected' : '';?>>8%</option>
            <option value="4" <?= $item['tax'] === 4 ? 'selected' : '';?>>対象外</option>
            <option value="5" <?= $item['tax'] === 5 ? 'selected' : '';?>>5%</option>
          </select>
        </div>
      </div>
    </div>

  </form>

<?php
/*
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $item->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $item->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Items'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="items form content">
            <?= $this->Form->create($item) ?>
            <fieldset>
                <legend><?= __('Edit Item') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    echo $this->Form->control('unit');
                    echo $this->Form->control('cost');
                    echo $this->Form->control('tax');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
*/
?>

@endsection
