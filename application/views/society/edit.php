<form method="post" name="<?=CONTROLLER?>" id="<?=$this->society->id?>">
	<div class="item">
		<div class="title"><label>基本信息：</label></div>
		<label>社团名称：</label><input name="people[name]" value="<?=$this->value('people/name'); ?>" type="text" placeholder="名称" />
		<label>教师：</label><?if($this->value('people/leader')){?><?=$this->people->fetch($this->value('people/leader'),'name')?><?}?>
		<label>名额：</label><input name="profiles[名额]" value="<?=$this->value('profiles/名额')?>" placeholder="名额" />
		<label>状态：</label>
<?if($this->user->inTeam('科训')){?>
		<select name="profiles[状态]">
			<?=options(array('内部招生','不限额开放报名','限额开放报名'),$this->value('profiles/状态'))?>
		</select>
<?}else{?>
		<?=$this->value('profiles/状态')?>
<?}?>
	</div>

	<div class="item">
		<div class="title"><label>简介：</label></div>
<?if($this->user->inTeam('teacher')){?>
		<textarea name="profiles[简介]" rows="7"><?=$this->value('profiles/简介')?></textarea>
<?}else{?>
		<div class="field"><?=$profiles['简介']?></div>
<?}?>
	</div>
	<div class="item" name="relative">
		<div class="title"><label>学生</label></div>
		<?=$relative_list?>
<?if($this->user->id==$this->value('people/leader') || $this->user->isLogged('societyadmin')){?>
		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="hidden" name="relative[id]" class="tagging" data-ajax="/student/match/" data-width="150px" value="<?=$this->value('relative/name')?>" placeholder="姓名" autocomplete-model="student" />
			<button type="submit" name="submit[relative]">添加</button>
		</span>
<?}?>
	 </div>

	<div class="item">
		<div class="title"><label>相关事务</label></div>
		<?=$project_list?>
	 </div>

	<div class="item">
		<div class="title"><label>备注：</label></div>
		<textarea name="people[comment]"><?=$this->value('people/comment')?></textarea>
	</div>
</form>
<?=$this->javascript('people_add')?>