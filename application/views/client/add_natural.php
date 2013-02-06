<form method="post" name="<?=CONTROLLER?>" id="<?=$this->client->id?>" enctype="multipart/form-data">
<div class="contentTableMenu">
	<div class="right">
		<input type="submit" name="submit[client]" value="保存" />
		<input type="submit" name="submit[cancel]" value="关闭" />
	</div>
</div>
<div class="contentTableBox">
	<div class="contentTable">
		<div class="item">
			<div class="title"><label>基本信息：</label></div>
			<input name="client[name]" value="<?=$this->value('client/name'); ?>" type="text" placeholder="中文名" />
			<select name="labels[类型]">
				<?=options($available_options['类型'],$this->value('labels/类型'),'类型')?>
			</select>

			<label><input type="checkbox" name="client[character]" value="单位" />单位</label>
			<select name="client[gender]"><?=options(array('男','女'), $this->value('client/gender'), '性别')?></select>
			<input type="text" name="client[id_card]" value="<?=$this->value('client/id_card'); ?>" placeholder="身份证" style="width:195px;" />
			<input type="text" name="client[birthday]" value="<?=$this->value('client/birthday'); ?>" placeholder="生日" class="date" />
			<input name="client[name_en]" value="<?=$this->value('client/name_en'); ?>" type="text" placeholder="英文名" />
			<br />
			<input type="text" name="client[work_for]" value="<?=$this->value('client/work_for')?>" placeholder="工作单位" />
			<input type="text" name="client[position]" value="<?=$this->value('client/position')?>" placeholder="职位" />
		</div>

		<div class="item">
			<div class="title"><label>来源：</label></div>
			<select name="source[type]">
				<?=options(array('_ENUM','client_source','type'),$this->value('source/type'))?>
			</select>
			
			<input type="text" name="source[detail]" value="<?=$this->value('source/detail')?>" <?if(!$this->value('source/detail')){?>class="hidden" disabled="disabled"<?}?> />
			<input type="text" name="client[staff_name]" placeholder="来源律师" value="<?=$this->value('client/staff_name')?$this->value('client/staff_name'):$this->user->name?>" />
		</div>

		<div class="item" name="profile">
			<div class="title"><label>资料项</label><label class="toggle-add-form">+</label></div>
			<?=$profile_list?>
			<div class="add-form hidden">
				<select name="profile[name]">
					<?=options($profile_name_options,$this->value('profile/name'),'资料项名称')?>
				</select>
				<input type="text" name="profile[content]" value="<?=$this->value('profile/content')?>" placeholder="资料项内容" />
				<input type="text" name="profile[comment]" value="<?=$this->value('profile/comment')?>" placeholder="备注" />

				<input type="submit" name="submit[profile]" value="添加" />
			</div>
		 </div>

		<div class="item" name="relative">
			<div class="title"><label>相关人</label><label class="toggle-add-form">+</label></div>
			<?=$relative_list?>
			<div class="add-form hidden">
				<input type="text" name="relative[name]" value="<?=$this->value('relative/name')?>" placeholder="名称" autocomplete-model="client" />
				<input name="relative[id]" class="hidden" />

				<select name="relative[relation]">
					<?=options(array('父','母',($this->value('client/gender')=='男'?'妻':'夫'),'亲属','朋友','其他','代理人'),$this->value('relative/relation'),'关系')?>
				</select>
				<span display-for="new" class="hidden">
					<?=checkbox('单位','relative[character]',$this->value('relative/character'),'单位')?>
		
					<input type="text" name="relative_profiles[电话]" value="<?=$this->value('relative_profiles/电话')?>" placeholder="电话" />
					<input type="text" name="relative_profiles[电子邮件]" value="<?=$this->value('relative_profiles/电子邮件')?>" placeholder="电子邮件" />
				</span>
				<input type="submit" name="submit[relative]" value="添加" />
			</div>
		 </div>

		<div class="item">
			<div class="title"><label>相关案件</label></div>
			<?=$case_list?>
		 </div>

		<div class="item">
			<div class="title"><label>备注：</label></div>
			<textarea name="client[comment]"><?=$this->value('client/comment')?></textarea>
		</div>

		<div class="submit">
			<input type="submit" name="submit[client]" value="保存" />
			<input type="submit" name="submit[cancel]" value="关闭" />
		</div>
	</div>
</div>
</form>
<?=javascript('client_add')?>