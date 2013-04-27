<form method="post" name="<?=CONTROLLER?>" id="<?=$this->project->id?>" enctype="multipart/form-data" encoding="multipart/form-data">
	<div class="item">
		<input type="text" name="project[name]" value="<?=$this->value('project/name')?>" placeholder="评价名称" class="large-field">
	 </div>

	<div class="item" name="status">
		<div class="title"><label>状态：</label>
			<span class="ui-dialog-buttonset" style="font-size:12px;">
				<input type="checkbox" id="active"<?if($this->value('project/active')){?> checked="checked"<?}?> name="project[active]" value="1" text-checked="开放" text-unchecked="停止" /><label for="active" ></label>
			</span>
		</div>
		<input type="text" name="project[time_contract]" value="<?=$this->value('project/time_contract')?>" class="date" placeholder="开始日期" />
		<input type="text" name="project[end]" value="<?=$this->value('project/end')?>" class="date" placeholder="结束日期" />
	</div>

	<div class="item" name="indicator">
		<div class="title"><label>评分项：</label>
			<select name="indicator_model" class="chosen" data-placeholder="模版">
				<?=options($this->evaluation->getModels(),NULL,'',true,false,false)?>
			</select>
			<button type="submit" name="apply_model">应用</button>
		</div>

		<?=$indicator_list?>

		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="text" name="indicator[name]" value="<?=$this->value('indicator/name');?>" placeholder="评分项" />
			<select name="indicator[type]" class="chosen" data-placeholder="类型" style="width:70px">
				<?=options(array('score'=>'分数','text'=>'文字'),$this->value('indicator/type'),NULL,true);?>
			</select>
			<input type="text" name="evaluation_indicator[weight]" value="<?=$this->value('evaluation_indicator/weight');?>" placeholder="分值" />
			<input type="text" name="evaluation_indicator[candidates]" value="<?=$this->value('evaluation_indicator/candidates');?>" placeholder="被评价人角色" />
			<input type="text" name="evaluation_indicator[judges]" value="<?=$this->value('evaluation_indicator/judges');?>" placeholder="评价人角色" />
			<button type="submit" name="submit[indicator]">添加</button>
		</span>
	</div>

	<div class="item" name="people">
		<div class="title"><label>人员：</label></div>

		<?=$people_list?>

		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="text" name="people[name]" value="<?=$this->value('people/name');?>" placeholder="姓名" autocomplete-model="people" />
			<select name="people[role]" class="chosen allow-new" data-placeholder="角色" style="width:150px;">
				<?=options($this->project->getAllRoles(),$this->value('people/role'),'角色');?>
			</select>
			<input name="people[id]" class="hidden" />
			<button type="submit" name="submit[people]">添加</button>
		</span>
	</div>

	<div class="item">
		<div class="title"><label>备注：</label></div>
		<textarea class="item" name="project[comment]" type="text" rows="3"><?=$this->value('project/comment')?></textarea>
	</div>
</form>
<?=javascript('project_add')?>