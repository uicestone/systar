<table class="contentTable search-bar" cellpadding="0" cellspacing="0" align="center">
	<thead><tr><td>年级班级</td></tr></thead>
	<tbody>
		<tr><td>
			<select name="grade" class="filter">
			<?=options(false,option('grade'),true,'grade',NULL,'name',"id>='".$this->school->highest_grade."'")?>
			</select>
		</td></tr>
		<tr><td>
			<select name="class" class="filter">
			<?=options(option('grade'),option('class'),true,'class','grade','name',"grade>='".$this->school->highest_grade."'")?>
			</select>
		</td></tr>
	</tbody>
</table>
<table class="contentTable search-bar" cellpadding="0" cellspacing="0" align="center">
	<thead><tr><td>考试</td></tr></thead>
	<tbody>
		<tr><td>
			<select name="exam" class="filter">
			<?=options(false,option('exam'),true,'exam',NULL,'name',"grade='".option('grade')."' ORDER BY id DESC")?>
			</select>
		</td></tr>
	</tbody>
</table>