<?php
$url_invoice = $this->Html->url("/invoice", true);
$add_url     = $url_invoice.'/add';
$edit_url    = $url_invoice.'/edit';
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
</style>

	<div id="search_wrap">
		<a href="javascript:void(0);" id="back_btn" class="btn btn-primary"><i class="fa fa-reply"></i> 戻る</a>
	</div>

	<table id="invoice_table">
		<thead>
			<tr>
				<th style="width: 15%">ステータス</th>
				<th style="width: 35%">文書</th>
				<th style="width: 15%">請求日</th>
				<th style="width: 15%">支払期限</th>
				<th style="width: 20%">金額</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($list as $row): ?>
			<tr>
				<td><span class="stat issued">未発行</span><span class="stat">未受注</span></td>
				<td>
					<a href="<?= $details_url . '/' . $row['invoice_headers']['invoice_no'];?>">
						<?= empty($row['invoice_headers']['title']) ? $row['invoice_headers']['destination'].' '.$row['invoice_headers']['responsible'].' '.$row['invoice_headers']['honor_title'] : $row['invoice_headers']['title']; ?>
					</a><br>
					<small style="color: #777;"><?= $row['invoice_headers']['invoice_no'];?></small>
					<?php if (!empty($row['invoice_headers']['note'])): ?>
						<small class="note"><?= mb_strimwidth($row['invoice_headers']['note'], 0, 28, '...');?></small>
					<?php endif; ?>
				</td>
				<td><?= date('Y/m/d', strtotime($row['invoice_headers']['issued_date']));?></td>
				<td><?= !empty($row['invoice_headers']['exp_date']) ? date('Y/m/d', strtotime($row['invoice_headers']['exp_date'])) : '-';?></td>
				<td><b><?= empty($row['invoice_headers']['price']) ? 0 : number_format($row['invoice_headers']['price']) ;?>円</b></td>
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

	$('#back_btn').on('click', function(event) {
		document.location.href = '<?= $url_invoice;?>';
	});

	$('.data-row').on('click', function(event) {
		var id = $(this).attr('data-id');
		$('body').append('<form id="edit"></form>');
		$('#edit').attr('action', '<?= $edit_url;?>') .attr('method','post');
		$('#edit').append('<input type="hidden" name="id" value="'+id+'">');
		$('#edit').submit();
	});

});
</script>
