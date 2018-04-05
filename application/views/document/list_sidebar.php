<?=$this->javascript('document_list')?>
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
				<button type="submit" name="search_cancel" tabindex="1"<?php if(!array_reduce($this->search_items, function($result, $item){return ($result || $this->config->user_item('search/'.$item));},false)){ ?> class="hidden"<?php } ?>>取消</button>
			</td>
		</tr>
	</tbody>
</table>
<input id="fileupload" type="file" name="document" data-url="/document/submit/upload" multiple="multiple" style="width:99%" />
<div id="progress" class="hidden"><div id="bar" style="width:0%;background:#007;height:1em;"></div></div>
<p class="upload-list-item hidden">
	<input type="text" name="document[name]" placeholder="名称" />
	<select name="labels[]" data-placeholder="标签" multiple="multiple" class="allow-new">
		<?=options($this->document->getAllLabels(NULL,$this->config->user_item('search/labels')), $this->config->user_item('search/labels'))?>
	</select>
	<hr />
</p>
<div id="upload-info"></div>
<button type="submit" id="save" class="major hidden">保存</button>