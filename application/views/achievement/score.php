<div class="contentTableMenu">
<?if(!is_logged('manager')){?>
	<button type="button" name="imfeelinglucky">手气不错</button>
<?}?>
	<?=$this->view('pagination',true,'pagination')?>
</div>
<div class="contentTableBox">
	<?=$list?>
</div>