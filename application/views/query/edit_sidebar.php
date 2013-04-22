<button type="submit" name="submit[query]" class="major">保存</button>
<button type="submit" name="submit[new_case]" class="major">立案</button>
<button type="submit" name="submit[file]" class="major">归档</button>
<select name="labels[]" class="chosen allow-new" data-placeholder="标签" multiple="multiple">
	<?=options($this->project->getAllLabels(),$labels)?>
</select>