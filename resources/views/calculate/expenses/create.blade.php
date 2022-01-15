@extends('layouts/default')
@section('page')

<div class="row">

    <div class="col s12">
        <h4 class="heading">旅費精算 > 精算</h4>
    </div>

    <div class="col s12">
        <?= $this->Form->create($expense, ['autocomplete' => 'off']) ?>
            <?php
                echo $this->Form->control('rel_id', ['type' => 'text', 'label' => '管理ID', 'value' => date('Ymd').'-'.'Num']);
                echo $this->Form->control('dir', ['label' => '出張先']);
                echo $this->Form->label('目的');
                echo $this->Form->textarea('purpose', ['class' => 'materialize-textarea']);
                echo $this->Form->label('交通費計');
                echo $this->Form->textarea('trans_fee', ['class' => 'materialize-textarea']);
                echo $this->Form->label('ガソリン代');
                echo $this->Form->textarea('gas_fee', ['class' => 'materialize-textarea']);
                echo $this->Form->label('日当計');
                echo $this->Form->textarea('daily_pay', ['class' => 'materialize-textarea']);
                echo $this->Form->label('宿泊費計');
                echo $this->Form->textarea('acm_fee', ['class' => 'materialize-textarea']);
                echo $this->Form->label('昼食計');
                echo $this->Form->textarea('lunch_fee', ['class' => 'materialize-textarea']);
                echo $this->Form->label('夕食計');
                echo $this->Form->textarea('dinner_fee', ['class' => 'materialize-textarea']);
                echo $this->Form->label('合計');
                echo $this->Form->textarea('total_fee', ['class' => 'materialize-textarea']);
                echo $this->Form->control('apply_date', ['label' => '申請日', 'value' => date('Y-m-d', strtotime('-1 week -1 day'))]);
                echo $this->Form->control('date_from', ['label' => '出発日', 'value' => date('Y-m-d', strtotime('-1 week'))]);
                echo $this->Form->control('date_to', ['label' => '帰着日', 'value' => date('Y-m-d', strtotime('-1 day'))]);
                echo $this->Form->control('pay_date', ['label' => '精算日', 'value' => date('Y-m-d', strtotime('now'))]);
                echo $this->Form->control('apply_person', ['label' => '申請者氏名']);
            ?>
        <button class="btn blue" type="submit"><i class="material-icons left">save</i>保存する</button>
        <?= $this->Form->end() ?>
    </div>
</div>

@endsection
