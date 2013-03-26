<button type="submit" name="submit[project]" class="major">保存</button>

<select data-placeholder="标签" multiple="multiple">
	<?=options($this->project->getAllLabels(),$labels)?>
</select>