<textarea type="text" name="content" placeholder="日程概要" class="text"><?=$this->value('schedule/content')?></textarea>
<input name="case_name" value="<?=$this->value('case_name')?>" placeholder="相关项目" class="text" autocomplete-model="cases" />
<input name="case" class="hidden" />
<input name="people" value="<?=$this->value('schedule/people')?>" placeholder="相关人员" class="text" autocomplete-model="people" />
<div class="profile hidden">
	<select class="profile-name" style="width:23%">
		<?=options(array('外出地点','费用金额','费用用途','备注'),NULL)?>
	</select>
	: 
	<input type="text" name="profiles[]" placeholder="信息内容" style="width:68%" />
</div>