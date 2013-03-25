<p id="<?=$file['id']?>"><?=$file['name']?>
	<input type="text" name="document[name]" placeholder="名称" />
	<select name="labels[]" data-placeholder="标签" multiple="multiple">
		<?=options($this->document->getAllLabels(), array_dir('_SESSION/document/index/search/labels'))?>
	</select>
	<hr />
</p>