<table class="contentTable search-bar">
	<thead><tr><th>搜索</th></tr></thead>
	<tbody>
		<tr>
			<td><input type="text" name="name" value="<?=$this->config->user_item('search/name')?>" placeholder="名称" title="名称" /></td>
		</tr>
		<tr>
			<td><select name="people" class="chosen allow-new" data-placeholder="职员"><?=options($this->staff->getArray(),$this->config->user_item('search/people'),'',true,false,false)?></select></td>
		</tr>
		<tr>
			<td>
				<select name="labels[]" class="chosen" data-placeholder="标签" multiple="multiple"><?=options($this->project->getAllLabels(),$this->config->user_item('search/labels'))?></select>
			</td>
		</tr>
		<tr><td><input type="text" name="date/from" value="<?=$this->config->user_item('search/date/from')?>" class="date" placeholder="开始日期" /></td></tr>
		<tr><td><input type="text" name="date/to" value="<?=$this->config->user_item('search/date/to')?>" class="date" placeholder="结束日期" /></td></tr>
		<tr>
			<td class="submit">
				<button type="submit" name="search" tabindex="0">搜索</button>
				<button type="submit" name="search_cancel" tabindex="1"<?if(!$this->config->user_item('search/name') && !$this->config->user_item('search/labels')){?> class="hidden"<?}?>>取消</button>
			</td>
		</tr>
	</tbody>
</table>