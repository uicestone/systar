<form method="post" name="<?=CONTROLLER?>" id="<?=$this->project->id?>">
	<div class="item">
		<input type="text" name="project[name]" value="<?=$this->value('project/name')?>" placeholder="名称" class="large-field"<?if($project['uid']!=$this->user->id){?> readonly="readonly"<?}?>>
	 </div>

<?if($project['summary'] || $project['uid']==$this->user->id){?>
	<div class="item">
		<textarea class="item" name="project[summary]" type="text" placeholder="描述" rows="4"<?if($project['uid']!=$this->user->id){?> readonly="readonly"<?}?>><?=$this->value('project/summary')?></textarea>
	</div>
<?}?>
	
	<div class="item" name="people">
		<div class="title"><label>人员：</label></div>

		<?=$people_list?>

		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="hidden" name="people[id]" class="tagging" data-ajax="/people/match/" data-placeholder="人员" />
			<select name="people[role]" class="chosen allow-new" data-placeholder="角色" style="width:150px;">
				<?=options($this->project->getRelatedRoles(),$this->value('people/role'),'',false,false,false);?>
			</select>
			<button type="submit" name="submit[people]">添加</button>
		</span>
	</div>

	<div class="item" name="document">
		<div class="title"><label>文件：</label></div>

		<?=$document_list?>

		<div class="add-form">
			<input type="file" name="document" id="file" data-url="/document/submit/upload" />
			<input name="document[id]" class="hidden" />
			<input type="text" name="document[name]" placeholder="文件名称" style="padding:4px" />
			<select name="document_labels[]" class="chosen allow-new" data-placeholder="标签" multiple="multiple" style="width:200px;height:15px;">
				<?=options($this->document->getAllLabels(),$this->value('document_labels'));?>
			</select>
			<button type="submit" name="submit[document]">保存</button>
		</div>
	</div>

	<div class="item" name="schedule">
		<div class="title">
			<span class="right">
				<?=(double)$this->schedule->getSum(array('project'=>$this->project->id,'completed'=>true))?>小时
				<a href="#schedule/lists?people=<?=$this->value('project/id')?>">所有日程>></a>
			</span>
			<label>日程：
				<a href="javascript:$.createSchedule({project:<?=$this->value('project/id')?>,refreshOnSave:true,target:this})">添加>></a>
				<a href="#schedule/lists?project=<?=$this->value('project/id')?>" class="right">查看全部</a>
			</label>
		</div>
		<?=$schedule_list?>
	</div>

	<div class="item">
		<div class="title"><label>备注：</label></div>
		<textarea class="item" name="project[comment]" type="text" rows="3"><?=$this->value('project/comment')?></textarea>
	</div>
</form>
<?=$this->javascript('project_add')?>