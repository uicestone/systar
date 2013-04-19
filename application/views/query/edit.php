<form method="post" name="query" id="<?=$this->query->id?>">
	<div class="item" name="client">
		<div class="title"><label>咨询人：</label></div>

		<input type="text" name="client[name]" value="<?=$this->value('client/name')?>" placeholder="姓名" autocomplete-model="client" />
		<input name="client[id]" class="hidden" value="<?=$this->value('client/id')?>" />
		<span class="hidden" display-for="new">
			<select name="client[gender]" disabled="disabled"><?=options(array('男','女'), $this->value('client/gender'), '性别')?></select>
		</span>
		<input type="text" name="cases[first_contact]" value="<?=$this->value('project/first_contact')?>" title="首次接待时间" placeholder="首次接待时间" class="date" />
		<select name="labels[咨询方式]">
			<?=options(array('面谈','电话','网络'),$this->value('labels/咨询方式'),'咨询方式')?>
		</select>
		<select name="labels[领域]">
			<?=options($this->config->item('案件领域'), $this->value('labels/领域'), '领域')?>
		</select>
	</div>

	<div class="item hidden" display-for="new">
		<div class="title"><label>来源：</label></div>
		<select name="client_profiles[来源类型]" disabled="disabled">
			<?=options($this->config->item('客户来源类型'),$this->value('client_profiles/来源类型'),'来源类型')?>
		</select>
		<input type="text" name="client_profiles[来源]" value="<?=$this->value('client_profiles/来源') ?>" class="hidden" placeholder="具体来源" disabled="disabled" locked-by="client_profiles[来源类型]" />
		<input type="text" name="client[staff_name]" value="<?=$this->value('client/staff_name')?$this->value('client/staff_name'):$this->user->name?>" title="来源律师" placeholder="来源律师" autocomplete-model="staff" disabled="disabled" />
	</div>

	<div class="item hidden" display-for="new">
		<div class="title"><label>联系方式：</label></div>
		<input type="text" name="client_profiles[电话]" value="<?=$this->value('client_contact_extra/phone'); ?>" title="电话" placeholder="电话" disabled="disabled" />
		<input type="text" name="client_profiles[电子邮件]" value="<?=$this->value('client_contact_extra/email'); ?>" title="电子邮件" placeholder="电子邮件" disabled="disabled" />
	</div>

	<div class="item">
		<div class="title"><label>跟进人员：</label></div>

		<input type="text" name="related_staff_name[督办人]" value="<?=$this->value('related_staff_name/督办人')?$this->value('related_staff_name/督办人'):$this->staff->getMyManager('name')?>" title="督办人" placeholder="督办人" autocomplete-model="staff" />

		<input type="text" name="related_staff_name[接洽律师]" value="<?=$this->value('related_staff_name/接洽律师')?$this->value('related_staff_name/接洽律师'):$this->user->name?>" title="接洽律师" placeholder="接洽律师" autocomplete-model="staff" />

		<input type="text" name="related_staff_name[律师助理]" value="<?=$this->value('related_staff_name/律师助理')?>" title="协助接洽" placeholder="协助接洽" autocomplete-model="staff" />

	</div>

	<div class="item">
		<div class="title"><label>概况：</label></div>
		<textarea name="cases[summary]" rows="7"><?=$this->value('project/summary'); ?></textarea>
	</div>

	<div class="item">
		<div class="title"><label>报价：</label></div>
		<input type="text" name="cases[quote]" value="<?=$this->value('project/quote');?>" style="width:99%;" />
	</div>

	<div class="item">
		<div class="title"><label>备注：</label></div>
		<textarea name="cases[comment]"><?=$this->value('project/comment'); ?></textarea>
	</div>
</form>
<?=javascript('query_add')?>