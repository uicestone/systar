<?=javascript('document_list')?>
<table class="contentTable search-bar">
	<thead><tr><th>搜索</th></tr></thead>
	<tbody>
		<tr>
			<td><input type="text" name="name" value="<?=$this->config->user_item('search/name')?>" placeholder="名称" title="名称" /></td>
		</tr>
		<tr>
			<td>
				<select name="labels[]" class="chosen allow-new" data-placeholder="标签" multiple="multiple"><?=options($this->document->getAllLabels(),$this->config->user_item('search/labels'))?></select>
			</td>
		</tr>
		<tr>
			<td class="submit">
				<button type="submit" name="search" tabindex="0">搜索</button>
				<button type="submit" name="search_cancel" tabindex="1"<?if(!$this->config->user_item('search/name') && !$this->config->user_item('search/labels')){?> class="hidden"<?}?>>取消</button>
			</td>
		</tr>
	</tbody>
</table>
<input id="fileupload" type="file" name="document" data-url="/document/submit" multiple="multiple" />
<p class="upload-list-item hidden">
	<input type="text" name="document[name]" placeholder="名称" />
	<select name="labels[]" data-placeholder="标签" multiple="multiple">
		<?=options($this->document->getAllLabels(NULL,$this->config->user_item('search/labels')), $this->config->user_item('search/labels'))?>
	</select>
	<hr />
</p>