<table class="contentTable search-bar" cellpadding="0" cellspacing="0" align="center">
	<thead><tr><th>年级班级</th></tr></thead>
	<tbody>
		<tr><td>
			<select name="grade" class="filter">
			<?=options(NULL,option('grade'),true,'team',NULL,'name',"type='grade' AND num>={$this->school->highest_grade}")?>
			</select>
		</td></tr>
		<tr><td>
			<select name="class" class="filter">
			<?=options($this->classes->getRelatedTeams(option('grade')),option('class'),true)?>
			</select>
		</td></tr>
	</tbody>
</table>