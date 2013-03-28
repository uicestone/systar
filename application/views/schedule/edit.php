<textarea type="text" name="content" placeholder="日程概要" class="text"><?=$this->value('schedule/content')?></textarea>
<select name="project" class="text">
	<?=options($this->project->getArray(array(),'name','id'),$this->value('project/id'),'相关事项',true)?>
</select>
<input name="people" value="<?=$this->value('schedule/people')?>" placeholder="相关人员" class="text" autocomplete-model="people" />
<div class="profile hidden">
	<select class="profile-name" style="width:23%">
		<?=options(array('外出地点','费用金额','费用用途','备注'),NULL)?>
	</select>
	: 
	<input type="text" name="profiles[]" placeholder="信息内容" style="width:68%" />
</div>