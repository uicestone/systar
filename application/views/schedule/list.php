<?=javascript('schedule_list')?>
<div class="contentTableMenu">
	<button type="button" name="export-excel">导出</button>
	<?=$this->view('pagination',true,'pagination')?>
</div>
<div class="contetTableBox">
	<?=$list?>
</div>
