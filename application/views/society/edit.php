<form method="post" name="<?=CONTROLLER?>" id="<?=$this->society->id?>">
	<div class="item">
		<div class="title"><label>基本信息：</label></div>
		<input name="people[name]" value="<?=$this->value('people/name'); ?>" type="text" placeholder="中文名" />
		<input name="people[abbreviation]" value="<?=$this->value('people/abbreviation')?>" placeholder="简称" />
		<input name="profiles[名额]" value="<?=$this->value('people/abbreviation')?>" placeholder="名额" />
	</div>

	<div class="item" name="relative">
		<div class="title"><label>学生</label></div>
		<?=$relative_list?>
		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="text" name="relative[name]" value="<?=$this->value('relative/name')?>" placeholder="名称" autocomplete-model="people" />
			<input name="relative[id]" class="hidden" />

			<select name="relative[relation]" class="chosen allow-new" data-placeholder="关系">
				<?=options($this->config->user_item(($this->value('people/character')=='单位'?'单位':'个人').'相关人关系'),$this->value('relative/relation'),'',false,false,false)?>
			</select>
			<span display-for="new" class="hidden">
				<?=checkbox('单位','relative[character]',$this->value('relative/character'),'单位')?>

				<input type="text" name="relative_profiles[电话]" value="<?=$this->value('relative_profiles/电话')?>" placeholder="电话" />
				<input type="text" name="relative_profiles[电子邮件]" value="<?=$this->value('relative_profiles/电子邮件')?>" placeholder="电子邮件" />
			</span>
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