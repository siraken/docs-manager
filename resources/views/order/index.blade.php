@extends('layouts/default')
@section('page')
<style>
.note {
	background: #eee;
	color: #111;
	border: solid 1px #ddd;
	padding: 3px 5px;
	display: block;
}
</style>

@csrf

<div class="row mb-3">
	<div class="col-12">
		<a href="/order/create" class="btn btn-primary">発注書を新しく作る</a>
		<a href="/order/trash" class="btn btn-primary">ごみ箱</a>
	</div>
</div>

<div class="row">
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
			<?php foreach ($estimates as $row): ?>
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
					<span class="status<?= $issued_status_class ?>" onclick="slipSetter.status(this, 'issued', <?= $row['id'] ?>, <?= empty($row['issued_flg']) ? '0' : $row['issued_flg'] ?>)"><i class="fa fa-check"></i><?= $issued_status ?></span>

					<!-- 受注状況 -->
					<?php
					$paid_status = '';
					$paid_status_class = ' ';
					switch ($row['is_paid']) {
						case 0:
							$paid_status = '未受注';
							$paid_status_class .= '';
							break;
						case 1:
							$paid_status = '受注済み';
							$paid_status_class .= 'set';
							break;
						case 2:
							$paid_status = '失注';
							$paid_status_class .= 'miss';
							break;
						default:
							$paid_status = '不明';
							$paid_status_class .= '';
							break;
					}
					?>
					<span class="status<?= $paid_status_class ?>"  onclick="slipSetter.status(this, 'paid', <?= $row['id'] ?>, <?= empty($row['paid_flg']) ? '0' : $row['paid_flg'] ?>)"><?= $paid_status ?></span>
				</td>
				<!-- 文書 -->
				<td>
					<a href="<?= '/estimates/' . $row['estimate_no'];?>">
						<?= $row['destination'] ?>
					</a><br>
					<small style="color: #777;">#<?= $row['estimate_no'];?></small>
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
					<b><?= empty($row['price']) ? 0 : number_format($row['price']) ;?>円</b>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if (empty($estimates)): ?>
		<p style="text-align: center;"><?= 'データがありません';?></p>
	<?php endif; ?>
</div>


@endsection
