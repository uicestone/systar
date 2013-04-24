<textarea name="content" placeholder="日程概要" style="width:97%"><?=$this->value('schedule/content')?></textarea>
<?if($this->input->get('project')===false || $this->input->get('project')){?>
<br />
<select name="project" data-placeholder="相关事务" style="width:97%">
	<?=options($this->project->getArray(array('people'=>$this->user->id),'name','id'),$this->value('project/id'),'',true)?>
</select>
<?}?>
<br />
<select name="people" data-placeholder="邀请其他人" multiple="multiple" style="width:97%"><?=options($this->user->getArray(array(
	'is_relative_of'=>$this->user->id,
	'has_relative_like'=>$this->user->id,
	'in_team'=>array_keys($this->user->teams),
	'in_related_team_of'=>array_keys($this->user->teams),
	'in_team_which_has_relative_like'=>array_keys($this->user->teams)
),'name','id'),$people,NULL,true)?></select>
<br />
<div class="profile hidden">
	<select class="profile-name" style="width:23%">
		<?=options(array('外出地点','费用金额','费用用途','备注'),NULL)?>
	</select>
	: 
	<input type="text" name="profiles[]" placeholder="信息内容" style="width:68%" />
</div>