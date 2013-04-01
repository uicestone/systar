<button type="submit" name="submit[people]" class="major">保存</button>
<select name="labels[]" class="chosen allow-new" data-placeholder="标签" multiple="multiple" style="width:239px;">
	<?=options($this->people->getAllLabels(),$labels)?>
</select>