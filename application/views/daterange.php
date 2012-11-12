<form method="post" name="date_range">
	<table class="contentTable search-bar" cellpadding="0" cellspacing="0" align="center">
		<thead><tr><th width="60px">日期</th><th>&nbsp;</th></tr></thead>
		<tbody>
			<tr><td>开始：</td><td><input type="text" name="date_from" value="<?=option('date_range/from')?>" class="date" /></td></tr>
			<tr><td>结束：</td><td><input type="text" name="date_to" value="<?=option('date_range/to')?>" class="date" /></td></tr>
			<input style="display:none;" name="date_field" value="<?=$date_field?>" />

			<tr><td colspan="2"><input type="submit" name="date_range" value="提交" />
				<input type="submit" name="date_range_cancel" value="取消" tabindex="1" />
			</td></tr>
		</tbody>
	</table>
</form>
