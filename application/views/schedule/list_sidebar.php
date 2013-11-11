<form>
	<table class="contentTable">
		<thead><tr><th>时间合计</th></tr></thead>
		<tbody>
			<tr>
				<td><?=$sum_time?></td>
			</tr>
		</tbody>
	</table>
	<table class="contentTable search-bar">
		<thead><tr><th>搜索</th></tr></thead>
		<tbody>
			<tr>
				<td><input type="text" name="name" value="<?=$this->config->user_item('search/name')?>" placeholder="名称" title="名称" /></td>
			</tr>
			<tr><td><input type="text" name="time/from" value="<?=$this->config->user_item('search/time/from')?>" class="date" placeholder="开始日期" /></td></tr>
			<tr><td><input type="text" name="time/to" value="<?=$this->config->user_item('search/time/to')?>" class="date" placeholder="结束日期" /></td></tr>
			<tr>
				<td>
					<input type="hidden" name="people" value="<?=$this->config->user_item('search/people')?>" class="tagging" style="width: 238px;" data-placeholder="人员" data-ajax="/user/match/" data-initselection='<?=$this->config->user_item('search/people')?json_encode($this->people->fetch($this->config->user_item('search/people'))):''?>' />
				</td>
			</tr>
			<tr>
				<td>
					<input type="hidden" name="project" value="<?=$this->config->user_item('search/project')?>" class="tagging" style="width: 238px;" data-placeholder="事务" data-ajax="/project/match/" data-initselection='<?=$this->config->user_item('search/project')?json_encode($this->project->fetch($this->config->user_item('search/project'))):''?>' />
				</td>
			</tr>
			<tr>
				<td>
					<select name="completed" class="chosen" data-placeholder="日志/日程">
						<?=options(array(0=>'日程',1=>'日志'),$this->config->user_item('search/completed'),'',true,false,false)?>
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