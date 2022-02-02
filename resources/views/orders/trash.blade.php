@extends('layouts/default')
@section('page')

@csrf

<div class="row mb-3">
	<div class="col-12">
		<a href="/orders/" class="btn btn-light border">戻る</a>
	</div>
</div>

<div class="row">
    <div class="col-12">
        <table class="table iv-table">
            <thead>
                <tr>
                    <th style="width: 10%;">ステータス</th>
                    <th style="width: 40%;">文書</th>
                    <th style="width: 15%;">発行日</th>
                    <th style="width: 15%;">有効期限</th>
                    <th style="width: 20%;">金額</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $row): ?>
                <tr>
                    <!-- ステータス -->
                    <td>
                        <!-- 発行状況 -->
                        <?php
                        $issued_status = '';
                        $issued_status_class = ' ';
                        switch ($row['is_issued']) {
                            case 0:
                                $issued_status = '未発行';
                                $issued_status_class .= '';
                                break;
                            case 1:
                                $issued_status = '発行済み';
                                $issued_status_class .= 'set';
                                break;
                            default:
                                $issued_status = '不明';
                                $issued_status_class .= '';
                                break;
                        }
                        ?>
                        <span class="status{{ $issued_status_class }}" onclick="slipSetter.status(this, 'issued', {{ $row['id'] }}, {{ empty($row['is_issued']) ? '0' : $row['is_issued'] }})">{!! $row['is_issued'] === 1 ? '<i class="fa fa-fw fa-check"></i>' : '' !!}{{ $issued_status }}</span>

                        <!-- 受注状況 -->
                        <?php
                        $ordered_status = '';
                        $ordered_status_class = ' ';
                        switch ($row['is_ordered']) {
                            case 0:
                                $ordered_status = '未受注';
                                $ordered_status_class .= '';
                                break;
                            case 1:
                                $ordered_status = '受注済み';
                                $ordered_status_class .= 'set';
                                break;
                            case 2:
                                $ordered_status = '失注';
                                $ordered_status_class .= 'miss';
                                break;
                            default:
                                $ordered_status = '不明';
                                $ordered_status_class .= '';
                                break;
                        }
                        ?>
                        <span class="status{{ $ordered_status_class }}"  onclick="slipSetter.status(this, 'ordered', {{ $row['id'] }}, {{ empty($row['is_ordered']) ? '0' : $row['is_ordered'] }})">{!! $row['is_ordered'] === 1 ? '<i class="fa fa-fw fa-check"></i>' : '' !!}{{ $ordered_status }}</span>
                    </td>
                    <!-- 文書 -->
                    <td>
                        <a href="<?= '/orders/edit/' . $row['id'];?>">
                            <?= $row['destination'] ?>
                        </a><br>
                        <small style="color: #777;">#<?= $row['order_no'];?></small>
                        <?php if (!empty($row['note'])): ?>
                            <small class="note"><?= mb_strimwidth($row['note'], 0, 28, '...');?></small>
                        <?php endif; ?>
                    </td>
                    <!-- 発行日 -->
                    <td>
                        <?= date('Y/m/d', strtotime($row['issued_date']));?>
                    </td>
                    <!-- 有効期限 -->
                    <td>
                        <?= !empty($row['exp_date']) ? date('Y/m/d', strtotime($row['exp_date'])) : '-';?>
                    </td>
                    <!-- 金額 -->
                    <td>
                        <b><?= empty($row['total_price']) ? 0 : number_format($row['total_price']) ;?>円</b>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        @if (empty($orders)):
            <p style="text-align: center;"><?= 'データがありません';?></p>
        <?php endif; ?>
    </div>
</div>

@endsection
