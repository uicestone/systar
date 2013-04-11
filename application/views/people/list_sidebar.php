<form>
	<table class="contentTable search-bar">
		<thead><tr><th>搜索</th></tr></thead>
		<tbody>
			<tr>
				<td><input type="text" name="name" value="<?=$this->config->user_item('search/name')?>" placeholder="名称" title="名称" /></td>
			</tr>
			<tr>
				<td>
					<select name="labels[]" class="chosen" data-placeholder="标签" title="输入多个标签，将采取“且”方式查找" multiple="multiple"><?=options($this->people->getAllLabels(),!$this->config->user_item('search/labels'))?></select>
				</td>
			</tr>
			<tr>
				<td>
					<select name="team[]" class="chosen" title="输入多个团组，将采取“或”方式查找" multiple="multiple" data-placeholder="团组">
						<?=options($this->team->getArray(array('people_type'=>$this->config->user_item('search/type')),'name','id'),$this->config->user_item('search/team'),NULL,true)?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="submit">
					<button type="submit" name="search" tabindex="0">搜索</button>
					<button type="submit" name="search_cancel" tabindex="1"<?if(!$this->config->user_item('search/name') && !$this->config->user_item('search/labels') && !$this->config->user_item('search/team')){?> class="hidden"<?}?>>取消</button>
				</td>
			</tr>
		</tbody>
	</table>
</form>