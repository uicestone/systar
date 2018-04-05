<div class="contentTableMenu">
<?php if(!is_logged('manager')){ ?>
	<button type="button" name="imfeelinglucky">手气不错</button>
<?php } ?>
	<?=$this->view('pagination',true,'pagination')?>
</div>
<div class="contentTableBox">
	<?=$list?>
</div>