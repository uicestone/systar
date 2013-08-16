<form>
	<table class="contentTable search-bar">
		<thead><tr><th>搜索</th></tr></thead>
		<tbody>
			<tr><td><input type="text" name="time/from" value="<?=$this->config->user_item('search/time/from')?>" class="date" placeholder="开始日期" /></td></tr>
			<tr><td><input type="text" name="time/to" value="<?=$this->config->user_item('search/time/to')?>" class="date" placeholder="结束日期" /></td></tr>
			<tr>
				<td class="submit">
					<button type="submit" name="search" tabindex="0">搜索</button>
					<button type="submit" name="search_cancel" tabindex="1"<?if(!array_reduce($this->search_items, function($result, $item){return ($result || $this->config->user_item('search/'.$item));},false)){?> class="hidden"<?}?>>取消</button>
				</td>
			</tr>
		</tbody>
	</table>
	<table class="contentTable">
		<thead><tr><th>工作日</th><th>基准工作时间</th></tr></thead>
		<tbody>
			<tr><td><?=$workdays?></td><td><?=$workdays * 6.5?></td></tr>
		</tbody>
	</table>
</form>