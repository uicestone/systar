<form>
	<table class="contentTable search-bar" cellpadding="0" cellspacing="0" align="center">
		<thead><tr><th width="80px">搜索</td></tr></thead>
		<tbody>
			<tr>
				<td><input type="text" name="name" value="<?=$this->config->user_item('search/name')?>" placeholder="名称" title="名称" /></td>
			</tr>
			<tr>
				<td>
					<select name="labels[]" class="chosen" data-placeholder="标签" multiple="multiple"><?=options($this->team->getAllLabels(),!$this->config->user_item('search/labels'))?></select>
				</td>
			</tr>
			<tr>
				<td>
					<select name="is_relative_of[]" class="chosen" multiple="multiple" data-placeholder="组">
						<?=options($this->team->getArray(array('has_relative'=>true),'name','id'),$this->config->user_item('search/is_relative_of'),NULL,true)?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="submit">
					<button type="submit" name="search" tabindex="0">搜索</button>
					<button type="submit" name="search_cancel" tabindex="1"<?if(!$this->config->user_item('search/name') && !$this->config->user_item('search/labels') && !$this->config->user_item('search/is_relative_of')){?> class="hidden"<?}?>>取消</button>
				</td>
			</tr>
		</tbody>
	</table>
</form>