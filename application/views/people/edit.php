<form method="post" name="<?=CONTROLLER?>" id="<?=$this->people->id?>" enctype="multipart/form-data">
	<div class="contentTableBox">
		<div class="item">
			<div class="title"><label>基本信息：</label></div>
			<input name="people[name]" value="<?=$this->value('people/name'); ?>" type="text" placeholder="中文名" />
			
			<select name="people[type]">
				<?=options($this->people->getTypes(),$this->value('people/type'),'人员类型')?>
			</select>
			
			<select name="labels[类型]">
				<?=options($available_options['类型'],$this->value('labels/类型'),'类型')?>
			</select>
			
			<?=checkbox('单位', 'people[character]', $this->value('people/character'), '单位')?>
<?if($this->value('people/character')=='单位'){?>
				<input name="people[abbreviation]" value="<?=$this->value('people/abbreviation')?>" placeholder="简称" />
<?}else{?>
				<select name="people[gender]"><?=options(array('男','女'), $this->value('people/gender'), '性别')?></select>
				<input type="text" name="people[id_card]" value="<?=$this->value('people/id_card'); ?>" placeholder="身份证" style="width:195px;" />
				<input type="text" name="people[birthday]" value="<?=$this->value('people/birthday'); ?>" placeholder="生日" class="date" />
				<input name="people[name_en]" value="<?=$this->value('people/name_en'); ?>" type="text" placeholder="英文名" />
				<br />
				<input type="text" name="people[work_for]" value="<?=$this->value('people/work_for')?>" placeholder="工作单位" />
				<input type="text" name="people[position]" value="<?=$this->value('people/position')?>" placeholder="职位" />
<?}?>
		</div>

<?if(!isset($people['type']) || $people['type']=='客户'){?>		
		<div class="item">
			<div class="title"><label>来源：</label></div>
			<select name="profiles[来源类型]">
				<?=options($this->config->item('客户来源类型'),$this->value('profiles/来源类型'),'来源类型')?>
			</select>
			
			<input type="text" name="profiles[来源]" value="<?=$this->value('profiles/来源')?>" <?if(!$this->value('profiles/来源')){?>class="hidden" disabled="disabled"<?}?> />
			<input type="text" name="people[staff_name]" placeholder="来源律师" value="<?=$this->value('people/staff_name')?$this->value('people/staff_name'):$this->user->name?>" />
		</div>
<?}?>

		<div class="item" name="profile">
			<div class="title"><label>资料项</label></div>
			<?=$profile_list?>
			<button type="button" class="toggle-add-form">＋</button>
			<span class="add-form hidden">
				<select name="profile[name]">
					<?=options($profile_name_options,$this->value('profile/name'),'资料项名称')?>
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
				<input type="text" name="relative[name]" value="<?=$this->value('relative/name')?>" placeholder="名称" autocomplete-model="people" />
				<input name="relative[id]" class="hidden" />

				<select name="relative[relation]">
					<?=options($this->config->item(($this->value('people/character')=='单位'?'单位':'个人').'相关人关系'),$this->value('relative/relation'),'关系',false,true)?>
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
			<div class="title"><label>相关案件</label></div>
			<?=$case_list?>
		 </div>

		<div class="item">
			<div class="title"><label>备注：</label></div>
			<textarea name="people[comment]"><?=$this->value('people/comment')?></textarea>
		</div>
	</div>
</form>
<?=javascript('people_add')?>