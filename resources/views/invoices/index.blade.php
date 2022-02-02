<?php
$url_invoice = "/";
$add_url     = $url_invoice.'/add';
$edit_url    = $url_invoice.'/edit';
$trash_url    = $url_invoice.'/trash';
$details_url    = $url_invoice.'/details';
?>
<style>
.note {
	background: #eee;
	color: #111;
	border: solid 1px #ddd;
	padding: 3px 5px;
	display: block;
}
.status {
	text-align: center;
}
</style>

	<div id="search_wrap">
		<a href="javascript:void(0);" id="add_btn" class="btn btn-primary"><i class="fa fa-plus-circle"></i> 請求書を新しく作る</a>
		<a href="javascript:void(0);" id="trash_btn" class="btn btn-primary"><i class="fa fa-trash-o"></i> ごみ箱</a>
	</div>

	<table class="table">
		<thead>
			<tr>
				<th style="width: 15%; text-align: center;">ステータス</th>
				<th style="width: 35%">文書</th>
				<th style="width: 15%">請求日</th>
				<th style="width: 15%">支払期限</th>
				<th style="width: 20%">金額</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($invoices as $row): ?>
			<tr>
				<td class="status">
					<?= $row['issued_flg'] == 0 ? '<span class="stat stat-issued" data-no="'.$row['invoice_no'].'" data-issued="0">未請求</span>' : '<span class="stat stat-issued set" data-no="'.$row['invoice_no'].'" data-issued="1"><i class="fa fa-check"></i> 請求済</span>';?>
					<?= $row['paid_flg'] == 0 ? '<span class="stat stat-paid" data-no="'.$row['invoice_no'].'" data-paid="0">未入金</span>' : '<span class="stat stat-paid set" data-no="'.$row['invoice_no'].'" data-paid="1"><i class="fa fa-check"></i> 入金済み</span>';?>
				</td>
				<td>
					<a href="<?= $details_url . '/' . $row['invoice_no'];?>">
						<?= empty($row['title']) ? $row['destination'].' '.$row['responsible'].' '.$row['honor_title'] : $row['title']; ?>
					</a><br>
					<small style="color: #777;"><?= $row['invoice_no'];?></small>
					<?php if (!empty($row['note'])): ?>
						<small class="note"><?= mb_strimwidth($row['note'], 0, 28, '...');?></small>
					<?php endif; ?>
				</td>
				<td><?= date('Y/m/d', strtotime($row['issued_date']));?></td>
				<td><?= !empty($row['exp_date']) ? date('Y/m/d', strtotime($row['exp_date'])) : '-';?></td>
				<td><b><?= empty($row['price']) ? 0 : number_format($row['price']) ;?>円</b></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if (empty($list)): ?>
		<p style="text-align: center;"><?= 'データがありません';?></p>
	<?php endif; ?>


<script type="text/javascript">
$(function(){

	$('#dt1').datepicker({ dateFormat:'yy/mm/dd', showButtonPanel: true });
	$('#dt2').datepicker({ dateFormat:'yy/mm/dd', showButtonPanel: true });

	$('#search_btn').on('click', function(event) {
		$('#search_form').submit();
	});

	$('#add_btn').on('click', function(event) {
		document.location.href = '<?= $add_url;?>';
	});

	$('#trash_btn').on('click', function(event) {
		document.location.href = '<?= $trash_url;?>';
	});

	$('.data-row').on('click', function(event) {
		var id = $(this).attr('data-id');
		$('body').append('<form id="edit"></form>');
		$('#edit').attr('action', '<?= $edit_url;?>') .attr('method','post');
		$('#edit').append('<input type="hidden" name="id" value="'+id+'">');
		$('#edit').submit();
	});

});

$(document).ready(function() {

	//請求状態を変更
	$(document).on('click', '.stat-issued', function() {

		let stat = $(this).data('issued');
		let num  = $(this).data('no');

		$.ajax({
			type: 'POST',
			datatype:'json',
			url: '<?= $url_invoice;?>/changeIssuedStat',
			data: {
				stat: stat,
				num:  num
			},
			success: function(data, dataType)
			{
				$.get(document.URL).done(function(data, textStatus, jqXHR) {
					let doc = new DOMParser().parseFromString(data, 'text/html');
					$('#invoice_table').html(doc.querySelector('#invoice_table'));
				});

				(stat == 0) ? console.log('issued_flg: 1') : console.log('issued_flg: 0');
			},
			error: function(XMLHttpRequest, textStatus, errorThrown)
			{
				alert('Error : ' + errorThrown);
			}
		});
		return false;
	});

	//入金状態を変更
	$(document).on('click', '.stat-paid', function() {

		let stat = $(this).data('paid');
		let num  = $(this).data('no');

		$.ajax({
			type: 'POST',
			datatype:'json',
			url: '<?= $url_invoice;?>/changePaidStat',
			data: {
				stat: stat,
				num:  num
			},
			success: function(data, dataType)
			{
				$.get(document.URL).done(function(data, textStatus, jqXHR) {
					let doc = new DOMParser().parseFromString(data, 'text/html');
					$('#invoice_table').html(doc.querySelector('#invoice_table'));
				});

				(stat == 0) ? console.log('paid_flg: 1') : console.log('paid_flg: 0');

			},
			error: function(XMLHttpRequest, textStatus, errorThrown)
			{
				alert('Error : ' + errorThrown);
			}
		});
		return false;
	});

});
</script>
