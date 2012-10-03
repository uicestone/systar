<? echo $search_bar?>
<table class="contentTable search-bar" cellpadding="0" cellspacing="0" align="center">
	<thead><tr><td>年级班级</td></tr></thead>
	<tbody>
		<tr><td>
			<select name="grade" class="filter">
			<? displayOption(NULL,option('grade'),true,'grade',NULL,'name',"id>='".$_SESSION['global']['highest_grade']."'")?>
			</select>
		</td></tr>
		<tr><td>
			<select name="class" class="filter">
			<? displayOption(option('grade'),option('class'),true,'class','grade','name',"grade>='".$_SESSION['global']['highest_grade']."'")?>
			</select>
		</td></tr>
	</tbody>
</table>