<textarea name="content" placeholder="日程概要" style="width:98%"><?=$this->value('schedule/content')?></textarea>
<?if($this->input->get('project')===false || $this->input->get('project')){?>
<select name="project" data-placeholder="相关事务" style="width:98%">
	<?=options($this->project->getArray(array('people'=>array_merge(array_keys($this->user->teams),array($this->user->id)),'active'=>true),'name','id'),$this->value('project/id'),'',true,false,false)?>
</select>
<br />
<?}?>
<input type="hidden" name="people" data-placeholder="邀请、通知或关联" class="tagging" multiple="multiple" value="<?=implode(',',$people)?>" data-initselection='<?=json_encode($this->people->getArray(array('id_in'=>$people)))?>' data-ajax="/people/match/"<?if($this->input->get('people')){?> changed="changed"<?}?> style="width:98%">
<br />
<select name="labels" data-placeholder="标签" multiple="multiple" class="allow-new" style="width:98%">
	<?=options($this->schedule->getAllLabels(),$this->value('labels'))?>
</select>
<hr />
<?if($this->input->get('period')){?>
<input type="text" name="start" value="<?=$this->value('schedule/start')?>" class="datetime" placeholder="开始时间" style="width:68%;margin-right:1%" /><input type="text" name="hours_own" value="<?=$this->value('schedule/hours_own')?>" placeholder="小时长" style="width:29%;" />
<br />
<?}?>
<input type="text" name="deadline" value="<?=$this->value('schedule/deadline')?>" class="datetime" placeholder="截止" style="width:98%" />
<br />
<div class="profile hidden" style="text-align:left;">
	<hr />
	<select class="profile-name allow-new" style="width:98%">
		<?=options(array('外出地点','费用金额','费用用途','备注'),NULL)?>
	</select>
	<br />
	<input type="text" name="profiles[]" style="width:98%" />
</div>
