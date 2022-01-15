@extends('layouts/default')
@section('page')

<div class="row">

    <div class="col s12">
        <h4 class="heading">出張申請 > 編集</h4>
    </div>

    <div class="col s12">
        <?= $this->Form->create($trip, ['autocomplete' => 'off']) ?>
        <?php
            echo $this->Form->control('rel_id', ['type' => 'text', 'label' => '管理ID']);
            echo $this->Form->control('dir', ['label' => '出張先']);
            echo $this->Form->label('目的');
            echo $this->Form->textarea('purpose', ['class' => 'materialize-textarea']);
            echo $this->Form->control('price', ['label' => '金額']);
            echo $this->Form->control('date_from', ['label' => '出発日']);
            echo $this->Form->control('date_to', ['label' => '帰着日']);
            echo $this->Form->control('apply_date', ['label' => '申請日']);
            echo $this->Form->control('apply_person', ['label' => '申請者氏名']);
        ?>
        <button class="btn blue" type="submit"><i class="material-icons left">save</i>保存する</button>
        <?= $this->Form->end() ?>
    </div>

</div>

@endsection
