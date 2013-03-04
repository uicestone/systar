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
				<td><button type="submit" name="search">搜索" tabindex="0</button>
<?if(option('in_search_mod')){?>
					<button type="submit" name="search_cancel">取消" tabindex="1</button>
<?}?>
				</td>
			</tr>
		</tbody>
	</table>
</form>