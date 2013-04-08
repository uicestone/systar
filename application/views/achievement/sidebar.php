<form method="post">
	<table class="contentTable search-bar" cellpadding="0" cellspacing="0" align="center">
		<thead><tr><th width="60px">日期</th></tr></thead>
		<tbody>
			<tr><td><input type="text" name="date_from" value="<?=$this->config->user_item('date/from')?>" class="date" placeholder="开始" /></td></tr>
			<tr><td><input type="text" name="date_to" value="<?=$this->config->user_item('date/to')?>" class="date" placeholder="结束" /></td></tr>

			<tr><td class="submit"><button type="submit" name="date_range">提交</button>
<?if($this->config->user_item('search/date_from') || $this->config->user_item('search/date_to')){?>
				<button type="submit" name="date_range_cancel" tabindex="1">取消</button>
<?}?>
			</td></tr>
		</tbody>
	</table>
</form>