<?php
$url_estimate = $this->Html->url("/estimate", true);
$add_url     = $url_estimate.'/add';
$edit_url    = $url_estimate.'/edit';
$details_url    = $url_estimate.'/details';
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

	<table id="estimate_table">
		<thead>
			<tr>
				<th style="width: 15%">ステータス</th>
				<th style="width: 35%">文書</th>
				<th style="width: 15%">発行日</th>
				<th style="width: 15%">有効期限</th>
				<th style="width: 20%">金額</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($list as $row): ?>
			<tr>
				<td><span class="stat issued">未発行</span><span class="stat">未受注</span></td>
				<td>
					<a href="<?= $details_url . '/' . $row['estimate_headers']['estimate_no'];?>">
						<?= empty($row['estimate_headers']['title']) ? $row['estimate_headers']['destination'].' '.$row['estimate_headers']['responsible'].' '.$row['estimate_headers']['honor_title'] : $row['estimate_headers']['title']; ?>
					</a><br>
					<small style="color: #777;"><?= $row['estimate_headers']['estimate_no'];?></small>
					<?php if (!empty($row['estimate_headers']['note'])): ?>
						<small class="note"><?= mb_strimwidth($row['estimate_headers']['note'], 0, 28, '...');?></small>
					<?php endif; ?>
				</td>
				<td><?= date('Y/m/d', strtotime($row['estimate_headers']['issued_date']));?></td>
				<td><?= !empty($row['estimate_headers']['exp_date']) ? date('Y/m/d', strtotime($row['estimate_headers']['exp_date'])) : '-';?></td>
				<td><b><?= empty($row['estimate_headers']['price']) ? 0 : number_format($row['estimate_headers']['price']) ;?>円</b></td>
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
		document.location.href = '<?= $url_estimate;?>';
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
