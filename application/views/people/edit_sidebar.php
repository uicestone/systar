<button type="submit" name="submit[people]" class="major">保存</button>
<select name="tags[]" class="chosen allow-new" data-placeholder="标签" multiple="multiple">
	<?=options($this->people->getAllTags(),$tags)?>
</select>