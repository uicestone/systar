<?=$this->javascript('document_list')?>
<table class="contentTable search-bar">
	<thead><tr><th>搜索</th></tr></thead>
	<tbody>
		<tr>
			<td><input type="text" name="name" value="<?=$this->config->user_item('search/name')?>" placeholder="名称" title="名称" /></td>
		</tr>
		<tr>
			<td>
				<select name="tags[]" class="chosen allow-new" data-placeholder="标签" multiple="multiple"><?=options($this->document->getAllTags(),$this->config->user_item('search/tags'))?></select>
			</td>
		</tr>
		<tr>
			<td class="submit">
				<button type="submit" name="search" tabindex="0">搜索</button>
				<button type="submit" name="search_cancel" tabindex="1"<?if(!array_reduce($this->search_items, function($result, $item){return ($result || $this->config->user_item('search/'.$item));},false)){?> class="hidden"<?}?>>取消</button>
			</td>
		</tr>
	</tbody>
</table>
<input id="fileupload" type="file" name="document" data-url="/document/submit/upload" multiple="multiple" style="width:99%" />
<p class="upload-list-item hidden">
	<input type="text" name="document[name]" placeholder="名称" />
	<select name="tags[]" data-placeholder="标签" multiple="multiple" class="allow-new">
		<?=options($this->document->getAllTags(NULL,$this->config->user_item('search/tags')), $this->config->user_item('search/tags'))?>
	</select>
	<hr />
</p>
<div id="upload-info"></div>
<button type="submit" id="save" class="major hidden">保存</button>