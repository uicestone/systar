<table class="contentTable search-bar">
	<thead><tr><th>搜索</th></tr></thead>
	<tbody>
		<tr>
			<td><input type="text" name="account" value="<?=$this->config->user_item('search/account')?>" placeholder="帐目编号" title="帐目编号" /></td>
		</tr>
		<tr>
			<td><input type="text" name="project_name" value="<?=$this->config->user_item('search/project_name')?>" placeholder="项目" title="项目" /></td>
		</tr>
		<tr>
			<td><input type="text" name="amount" value="<?=$this->config->user_item('search/amount')?>" placeholder="金额" title="金额" /></td>
		</tr>
		<tr><td><input type="text" name="date/from" value="<?=$this->config->user_item('search/date/from')?>" class="date" placeholder="开始日期" /></td></tr>
		<tr><td><input type="text" name="date/to" value="<?=$this->config->user_item('search/date/to')?>" class="date" placeholder="结束日期" /></td></tr>
		<tr>
			<td><input type="text" name="payer_name" value="<?=$this->config->user_item('search/payer_name')?>" placeholder="付款/收款人" title="付款/收款人" /></td>
		</tr>
		<tr>
			<td>
				<select name="labels[]" class="chosen" data-placeholder="标签" multiple="multiple"><?=options($this->account->getAllLabels(),!$this->config->user_item('search/labels'))?></select>
			</td>
		</tr>
		<tr>
			<td>
				<select name="received">
					<?=options(array(0=>'预计',1=>'实际'),$this->config->user_item('search/received'),null,true)?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="submit">
				<button type="submit" name="search" tabindex="0">搜索</button>
				<button type="submit" name="search_cancel" tabindex="1" >取消</button>
			</td>
		</tr>
	</tbody>
</table>
<?=$this->table->generate($summary)?>