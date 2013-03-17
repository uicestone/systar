<button type="submit" name="submit[people]" class="major">保存</button>
<select id="labels" data-placeholder="标签" multiple="multiple" style="width:239px;">
	<?=options($this->people->getAllLabels(),$labels)?>
</select>
