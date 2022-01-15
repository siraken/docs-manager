@extends('layouts/default')
@section('page')

<div class="row">

    <div class="col s12">
        <h4 class="heading">旅費精算 > 詳細</h4>
        <table>
            <tr>
                <th>管理ID</th>
                <td><?= h($expense->rel_id) ?></td>
            </tr>
            <tr>
                <th>出張先</th>
                <td><?= h($expense->dir) ?></td>
            </tr>
            <tr>
                <th>目的</th>
                <td><?= h($expense->purpose) ?></td>
            </tr>
            <tr>
                <th>申請者</th>
                <td><?= h($expense->apply_person) ?></td>
            </tr>
            <!-- description start -->
            <tr>
                <th>交通費計</th>
                <td><?= h($expense->trans_fee) ?></td>
            </tr>
            <tr>
                <th>ガソリン代</th>
                <td><?= h($expense->gas_fee) ?></td>
            </tr>
            <tr>
                <th>日当計</th>
                <td><?= h($expense->daily_pay) ?></td>
            </tr>
            <tr>
                <th>宿泊費計</th>
                <td><?= h($expense->acm_fee) ?></td>
            </tr>
            <tr>
                <th>昼食計</th>
                <td><?= h($expense->lunch_fee) ?></td>
            </tr>
            <tr>
                <th>夕食計</th>
                <td><?= h($expense->dinner_fee) ?></td>
            </tr>
            <tr>
                <th>合計金額</th>
                <td><?= h($expense->total_fee) ?></td>
            </tr>
            <!-- description end -->
            <tr>
                <th>申請日</th>
                <td><?= h($expense->apply_date) ?></td>
            </tr>
            <tr>
                <th>出発日</th>
                <td><?= h($expense->date_from) ?></td>
            </tr>
            <tr>
                <th>帰着日</th>
                <td><?= h($expense->date_to) ?></td>
            </tr>
            <tr>
                <th>精算日</th>
                <td><?= h($expense->pay_date) ?></td>
            </tr>
        </table>
    </div>

</div>

@endsection
