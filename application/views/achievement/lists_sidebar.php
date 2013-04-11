<form method="post" name="date_range">
	<table class="contentTable search-bar">
		<thead><tr><th width="60px">日期</th></tr></thead>
		<tbody>
			<tr><td><input type="text" name="date_from" value="<?=$this->config->user_item('search/date_from')?>" class="date" placeholder="开始" /></td></tr>
			<tr><td><input type="text" name="date_to" value="<?=$this->config->user_item('search/date_to')?>" class="date" placeholder="结束" /></td></tr>
			<tr>
				<td>
					<select name="contribute_type">
						<?=options(array('fixed'=>'固定贡献','actual'=>'实际贡献'),$this->config->user_item('search/contribute_type'))?>
					</select>
				</td>
			</tr>

			<tr><td class="submit"><button type="submit" name="date_range">提交</button>
<?if($this->config->user_item('search/date_from') || $this->config->user_item('search/date_to')){?>
				<button type="submit" name="date_range_cancel" tabindex="1">取消</button>
<?}?>
			</td></tr>
		</tbody>
	</table>
</form>
<div>
<?=$this->table->generate($achievement_dashboard)?>
</div>
<div>
<?=$this->table->generate($achievement_sum)?>
</div>