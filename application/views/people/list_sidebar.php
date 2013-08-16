<form>
	<table class="contentTable search-bar">
		<thead><tr><th>搜索</th></tr></thead>
		<tbody>
			<tr>
				<td><input type="text" name="name" value="<?=$this->config->user_item('search/name')?>" placeholder="名称" title="名称" /></td>
			</tr>
			<tr>
				<td>
					<select name="tags[]" class="chosen" data-placeholder="标签" title="输入多个标签，将采取“且”方式查找" multiple="multiple"><?=options($this->people->getAllTags(),$this->config->user_item('search/tags'))?></select>
				</td>
			</tr>
			<tr>
				<td>
					<select name="in_team[]" class="chosen" title="输入多个团组，将采取“或”方式查找" multiple="multiple" data-placeholder="团组">
						<?=options($this->group->getArray(array('people_type'=>$this->config->user_item('search/type')),'name','id'),$this->config->user_item('search/in_team'),NULL,true)?>
					</select>
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
</form>