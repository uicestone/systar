<button type="submit" name="submit[account]" class="major">保存</button>
<select name="tags[]" class="chosen allow-new" data-placeholder="标签" multiple="multiple">
	<?=options($this->account->getAllTags(),$tags)?>
</select>