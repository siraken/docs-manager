@extends('layouts/default')
@section('page')

<div class="row mb-3">
	<div class="col-12">
        <h4 class="heading">旅費精算</h4>
		<a class="btn btn-light border" href="{{ route('expenses.create') }}"><i class="bi bi-plus-circle me-2"></i>旅費精算をする</a>
        <a class="btn btn-light border" href="{{ route('expenses.create') }}"><i class="bi bi-download me-2"></i>CSV取り込み</a>
	</div>
</div>

<div class="row">

    <div class="col-12">
        <table class="table">
            <thead>
                <tr>
                    <th>申請日</th>
                    <th>出張先</th>
                    <th class="hide-on-small-only">目的</th>
                    <th class="hide-on-small-only">精算日</th>
                    <th>申請者</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expenses as $expense): ?>
                <tr>
                    <td><?= date('Y/m/d', strtotime($expense->apply_date)) ?></td>
                    <td><?= $expense->dir ?></td>
                    <td class="hide-on-small-only"><?= mb_strimwidth($expense->purpose, 0, 30, "...") ?></td>
                    <td class="hide-on-small-only"><?= date('Y/m/d', strtotime($expense->pay_date)) ?></td>
                    <td><?= $expense->apply_person ?></td>
                    <td>
                        <ul id="optionDropdown<?=$expense->id?>" class="dropdown-content">
                            <li><a href="/expense/pdf/<?=$expense->id?>">PDF</a></li>
                            <li><a href="/expense/view/<?=$expense->id?>">View</a></li>
                            <li><a href="/expense/edit/<?=$expense->id?>">Edit</a></li>
                            <li><?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $expense->id], ['confirm' => __('Are you sure you want to delete # {0}?', $expense->id)]) ?></li>
                        </ul>
                        <a class="btn blue dropdown-trigger" href="#!" data-target="optionDropdown<?=$expense->id?>"><i class="material-icons">settings</i><i class="material-icons right">arrow_drop_down</i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

@endsection
