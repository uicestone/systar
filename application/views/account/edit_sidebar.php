<?php if($this->user->isLogged('finance')){ ?>
<button type="submit" name="submit[account]" class="major">保存</button>
<?php } ?>
<select name="labels[]" class="chosen allow-new" data-placeholder="标签" multiple="multiple">
	<?=options($this->account->getAllLabels(),$labels)?>
</select>