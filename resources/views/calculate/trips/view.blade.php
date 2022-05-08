@extends('layouts/default')
@section('page')

<div class="row">

    <div class="col s12">
        <h4 class="heading">出張申請 > 詳細</h4>
        <table>
            <tr>
                <th>管理ID</th>
                <td>
                    <?= h($trip->rel_id) ?>
                </td>
            </tr>
            <tr>
                <th>出張先</th>
                <td>
                    <?= h($trip->dir) ?>
                </td>
            </tr>
            <tr>
                <th>目的</th>
                <td>
                    <?= h($trip->purpose) ?>
                </td>
            </tr>
            <tr>
                <th>申請者</th>
                <td>
                    <?= h($trip->apply_person) ?>
                </td>
            </tr>
            <tr>
                <th>金額</th>
                <td>¥
                    <?= $this->Number->format($trip->price) ?>
                </td>
            </tr>
            <tr>
                <th>出発日</th>
                <td>
                    <?= date('Y年m月d日', strtotime(h($trip->date_from))) ?>
                </td>
            </tr>
            <tr>
                <th>帰着日</th>
                <td>
                    <?= date('Y年m月d日', strtotime(h($trip->date_to))) ?>
                </td>
            </tr>
            <tr>
                <th>申請日</th>
                <td>
                    <?= date('Y年m月d日', strtotime(h($trip->apply_date))) ?>
                </td>
            </tr>
        </table>
    </div>

</div>

@endsection
