<form method="post" name="search">
	<table class="contentTable search-bar" cellpadding="0" cellspacing="0" align="center">
		<thead><tr><th width="80px">搜索</td></tr></thead>
		<tbody>
<?foreach($search_fields as $field_table_name => $field_ui_name){?>
			<tr>
				<td><input type="text" name="keyword[<?=$field_table_name?>]" value="<?=option('keyword/'.$field_table_name)?>" placeholder="<?=$field_ui_name?>" title="<?=$field_ui_name?>" /><br /></td>
			</tr>
<?}?>
			<tr>
				<td><input type="submit" name="search" value="搜索" tabindex="0" />
<?if(option('in_search_mod')){?>
					<input type="submit" name="search_cancel" value="取消" tabindex="1" />
<?}?>
				</td>
			</tr>
		</tbody>
	</table>
</form>