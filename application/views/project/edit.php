<form method="post" name="<?=CONTROLLER?>" id="<?=$this->project->id?>" enctype="multipart/form-data" encoding="multipart/form-data">
<div class="contentTableBox">
	<div class="item">
		<input type="text" name="project[name]" value="<?=$this->value('project/name')?>" placeholder="事项名称" style="width:99.4%;font-size:20px;">
	 </div>

	<div class="item" name="people">
		<div class="title"><label>人员：</label></div>

		<?=$people_list?>

		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="text" name="people[name]" value="<?=$this->value('people/name');?>" placeholder="姓名" autocomplete-model="people" />
			<select name="people[role]" data-placeholder="角色">
				<?=options($this->project->getAllRoles(),$this->value('staff/role'),'角色');?>
			</select>
			<input name="people[id]" class="hidden" />
			<button type="submit" name="submit[people]">添加</button>
		</span>
	</div>

	<div class="item" name="document">
		<div class="title"><label>文件：</label></div>

		<?=$document_list?>

		<div class="add-form">
			<input type="file" name="document" id="file" data-url="/document/submit" />
			<select name="document_labels[]" data-placeholder="标签" multiple="multiple">
				<?=options($this->document->getAllLabels(),$this->value('document_labels'));?>
			</select>
			<input type="text" name="document[name]" placeholder="文件名称" />
			<button type="submit" name="submit[document]">保存</button>
		</div>
	</div>

	<div class="item" name="schedule">
		<div class="title"><label>日程：</label></div>
		<?=$schedule_list?>
	</div>

	<div class="item">
		<div class="title"><label>事项概述：</label></div>
		<textarea class="item" name="project[summary]" type="text" rows="4"><?=$this->value('project/summary')?></textarea>
	</div>

	<div class="item">
		<div class="title"><label>备注：</label></div>
		<textarea class="item" name="project[comment]" type="text" rows="3"><?=$this->value('project/comment')?></textarea>
	</div>
</div>
</form>
<?=javascript('project_add')?>