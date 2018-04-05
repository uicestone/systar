<?php if($this->config->user_item('pagination/rows')){ ?>
<div class="contentTableMenu">
	<?=$this->view('pagination',true,'pagination')?>
</div>
<?php } ?>
<div class="contentTableBox">
	<?=$this->table->generate()?>
</div>