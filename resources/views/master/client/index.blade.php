<?php
$url_estimate = "/";
$add_url     = $url_estimate.'/add';
$m_add_url     = $url_estimate.'/multi_add';
$edit_url    = $url_estimate.'/edit';
$delete_url    = $url_estimate.'/delete';
?>

	<div id="search_wrap">
		<a href="items/add" id="add_btn" class="btn btn-primary"><i class="fa fa-plus-circle"></i> 品目の新規登録</a>
		<!--
		<a href="javascript:void(0);" id="m_add_btn" class="btn btn-primary">複数登録</a>
		-->
		<div class="clearfix"></div>
		<?php // $this->Session->flash('success'); ?>
		<?php // $this->Session->flash('warning'); ?>
		<?php // $this->Session->flash('error'); ?>
	</div>

	<table class="table">
		<thead>
			<tr>
				<th style="width: 35%;">品番・品名</th>
				<th style="width: 10%;">単位</th>
				<th style="width: 20%;">単価</th>
				<th style="width: 20%;">税区分</th>
				<th style="width: 15%;">操作</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($items as $row) : ?>
			<tr>
				<td><?= $row['name'];?></td>
				<td><?= $row['unit'];?></td>
				<td><?= number_format($row['cost']);?>円</td>
				<td>
					<?php
						if ($row['tax'] == null || $row['tax'] == 0) {
							$taxType = 0;
						} else { $taxType = $row['tax']; }

						switch($taxType) {
							case 0:  echo     '-'; break;
							case 1:  echo   '10%'; break;
							case 2:  echo '軽減8%'; break;
							case 3:  echo    '8%'; break;
							case 4:  echo '対象外'; break;
							case 5:  echo    '5%'; break;
							default: echo     '-'; break;
						}
					?>
				</td>
				<td>
					<a href="items/edit/<?= $row['id'];?>">編集</a>
					<a href="javascript:void(0);" onclick="deleteItem(<?= $row['id'];?>);">削除</a>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

<script type="text/javascript">

let deleteItem = (id) => {
	if (confirm('削除します。よろしいですか？')) {
		location.href = "items/delete/" + id;
	}
};

$(function(){

	$('#dt1').datepicker({ dateFormat:'yy/mm/dd', showButtonPanel: true });
	$('#dt2').datepicker({ dateFormat:'yy/mm/dd', showButtonPanel: true });

	$('#search_btn').on('click', function(event) {
		$('#search_form').submit();
	});

	$('#add_btn').on('click', function(event) {
		document.location.href = '<?= $add_url;?>';
	});

	$('#m_add_btn').on('click', function(event) {
		document.location.href = '<?= $m_add_url;?>';
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

<?php
/*
<div class="items index content">
    <?= $this->Html->link(__('New Item'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Items') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('unit') ?></th>
                    <th><?= $this->Paginator->sort('cost') ?></th>
                    <th><?= $this->Paginator->sort('tax') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= $this->Number->format($item->id) ?></td>
                    <td><?= h($item->name) ?></td>
                    <td><?= h($item->unit) ?></td>
                    <td><?= $this->Number->format($item->cost) ?></td>
                    <td><?= $this->Number->format($item->tax) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $item->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $item->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $item->id], ['confirm' => __('Are you sure you want to delete # {0}?', $item->id)]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>
*/
?>
