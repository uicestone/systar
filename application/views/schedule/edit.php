<textarea name="content" placeholder="日程概要" style="width:98%"><?=$this->value('schedule/content')?></textarea>
<?if($this->input->get('project')===false || $this->input->get('project')){?>
<select name="project" data-placeholder="相关事务" style="width:98%">
	<?=options($this->project->getArray(array('people'=>$this->user->id),'name','id'),$this->value('project/id'),'',true)?>
</select>
<?}?>
<select name="people" data-placeholder="邀请其他人" multiple="multiple" style="width:98%"><?=options($this->user->getArray(array(
	'is_relative_of'=>$this->user->id,
	'has_relative_like'=>$this->user->id,
	'in_team'=>array_keys($this->user->teams),
	'in_related_team_of'=>array_keys($this->user->teams),
	'in_team_which_has_relative_like'=>array_keys($this->user->teams)
),'name','id'),$people,NULL,true)?></select>
<?if($this->input->get('period')){?>
<input type="text" name="start" value="<?=$this->value('schedule/start')?>" class="datetime" placeholder="开始时间" style="width:68%;margin-right:1%" /><input type="text" name="hours_own" value="<?=$this->value('schedule/hours_own')?>" placeholder="小时长" style="width:29%;" />
<?}?>
<input type="text" name="deadline" value="<?=$this->value('schedule/deadline')?>" class="datetime" placeholder="截止" style="width:98%" />
<div class="profile hidden">
	<select class="profile-name allow-new" style="width:35%">
		<?=options(array('外出地点','费用金额','费用用途','备注'),NULL)?>
	</select>
	: 
	<input type="text" name="profiles[]" placeholder="信息内容" style="width:60%" />
</div>
