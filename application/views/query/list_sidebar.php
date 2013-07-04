<form>
	<table class="contentTable search-bar">
		<thead><tr><th>搜索</th></tr></thead>
		<tbody>
			<tr>
				<td><input type="text" name="name" value="<?=$this->config->user_item('search/name')?>" placeholder="名称" title="名称" /></td>
			</tr>
			<tr>
				<td><input type="text" name="num" value="<?=$this->config->user_item('search/num')?>" placeholder="案号" title="案号" /></td>
			</tr>
			<tr>
				<td>
					<select name="labels[]" class="chosen" data-placeholder="标签" multiple="multiple"><?=options($this->project->getAllLabels(),$this->config->user_item('search/labels'))?></select>
				</td>
			</tr>
			<tr>
				<td><select name="people[]" multiple="multiple" class="chosen allow-new" data-placeholder="职员"><?=options($this->staff->getArray(),$this->config->user_item('search/people'),NULL,true)?></select></td>
			</tr>
			<tr><td><input type="text" name="first_contact[from]" value="<?=$this->config->user_item('search/first_contact/from')?>" class="date" placeholder="首次接待日期自" /></td></tr>
			<tr><td><input type="text" name="first_contact[to]" value="<?=$this->config->user_item('search/first_contact/to')?>" class="date" placeholder="首次接待日期至" /></td></tr>
			<tr>
				<td class="submit">
					<button type="submit" name="search" tabindex="0">搜索</button>
					<button type="submit" name="search_cancel" tabindex="1"<?if(!array_reduce($this->search_items, function($result, $item){return ($result || $this->config->user_item('search/'.$item));},false)){?> class="hidden"<?}?>>取消</button>
				</td>
			</tr>
		</tbody>
	</table>
</form>