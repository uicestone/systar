<button type="submit" name="submit[document]" class="major">保存</button>
<select name="labels[]" class="chosen allow-new" data-placeholder="标签" multiple="multiple">
	<?=options($this->document->getAllLabels(),$labels)?>
</select>
<button type="submit" name="submit[delete]" class="major">删除</button>