<table class="contentTable search-bar" cellpadding="0" cellspacing="0" align="center">
	<thead><tr><th>年级班级</th></tr></thead>
	<tbody>
		<tr><td>
			<select name="grade" class="filter">
			<? displayOption(NULL,option('grade'),true,'grade',NULL,'name',"id>='".$this->school->highest_grade."'")?>
			</select>
		</td></tr>
		<tr><td>
			<select name="class" class="filter">
			<? displayOption(option('grade'),option('class'),true,'class','grade','name',"grade>='".$this->school->highest_grade."'")?>
			</select>
		</td></tr>
	</tbody>
</table>