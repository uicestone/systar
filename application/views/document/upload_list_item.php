<p><?=$file['name']?>
	<select data-placeholder="<?=$file['name']?>的标签" multiple="multiple">
		<?=options($this->document->getAllLabels(), $_SESSION['document']['index']['search']['labels'])?>
	</select>
	<input type="text" name="comment" placeholder="备注" />
	<hr />
</p>