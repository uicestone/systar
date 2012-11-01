<form method="post" name="search">
	<table class="contentTable search-bar" cellpadding="0" cellspacing="0" align="center">
		<thead><tr><th width="80px">搜索</td><td>&nbsp;</th></tr></thead>
		<tbody>
<?foreach($search_fields as $field_table_name => $field_ui_name){?>
			<tr>
				<td><label><?=$field_ui_name?>：</label></td>
				<td><input type="text" name="keyword[<?=$field_table_name?>]" value="<?=option('keyword/'.$field_table_name)?>" /><br /></td>
			</tr>
<?}?>
			<tr>
				<td colspan="2"><input type="submit" name="search" value="搜索" tabindex="0" />
<?if(option('in_search_mod')){?>
					<input type="submit" name="search_cancel" value="取消" tabindex="1" />
<?}?>
				</td>
			</tr>
		</tbody>
	</table>
</form>