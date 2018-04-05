<form method="post" name="<?=CONTROLLER?>" id="<?=$this->people->id?>" enctype="multipart/form-data">
	<div class="item">
		<div class="title"><label>基本信息：</label></div>
		<input name="people[name]" value="<?=$this->value('people/name'); ?>" type="text" placeholder="中文名" />

		<input name="people[phone]" value="<?=$this->value('people/phone'); ?>" type="text" placeholder="电话" />
		<input name="people[email]" value="<?=$this->value('people/email'); ?>" type="text" placeholder="电子邮件" />
		<?=checkbox('单位', 'people[character]', $this->value('people/character'), '单位')?>
<?php if($this->value('people/character')=='单位'){ ?>
		<input name="people[abbreviation]" value="<?=$this->value('people/abbreviation')?>" placeholder="简称" />
<?php }else{ ?>
		<select name="people[gender]"><?=options(array('男','女'), $this->value('people/gender'), '性别')?></select>
		<input type="text" name="people[id_card]" value="<?=$this->value('people/id_card'); ?>" placeholder="身份证" style="width:195px;" />
		<input type="text" name="people[birthday]" value="<?=$this->value('people/birthday'); ?>" placeholder="生日" class="date" />
		<input name="people[name_en]" value="<?=$this->value('people/name_en'); ?>" type="text" placeholder="英文名" />
		<input type="text" name="people[work_for]" value="<?=$this->value('people/work_for')?>" placeholder="工作单位" />
		<input type="text" name="people[position]" value="<?=$this->value('people/position')?>" placeholder="职位" />
<?php } ?>

	</div>

	<div class="item" name="profile">
		<div class="title"><label>资料项</label></div>
		<?=$profile_list?>
		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<select name="profile[name]" class="chosen allow-new" data-placeholder="资料项名称">
				<?=options($profile_name_options,$this->value('profile/name'),'',false,false,false)?>
			</select>
			<input type="text" name="profile[content]" value="<?=$this->value('profile/content')?>" placeholder="资料项内容" />
			<input type="text" name="profile[comment]" value="<?=$this->value('profile/comment')?>" placeholder="备注" />

			<button type="submit" name="submit[profile]">添加</button>
		</span>
	 </div>

	<div class="item" name="relative">
		<div class="title"><label>相关人</label></div>
		<?=$relative_list?>
		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="hidden" name="relative[id]" class="tagging" data-placeholder="名称" data-ajax="/people/match/" />
			<select name="relative[relation]" class="chosen allow-new" data-placeholder="关系">
				<?=options($this->config->user_item(($this->value('people/character')=='单位'?'单位':'个人').'相关人关系'),$this->value('relative/relation'),'',false,false,false)?>
			</select>
			<button type="submit" name="submit[relative]">添加</button>
		</span>
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