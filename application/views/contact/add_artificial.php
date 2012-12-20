<? javascript('contact_add')?>
<form method="post">
<div class="contentTableMenu">
	<div class="right">
		<input type="submit" name="submit[contact]" value="保存" />
		<input type="submit" name="submit[cancel]" value="关闭" />
	</div>
</div>
<div class="contentTableBox">
	<div class="contentTable">
		<div class="item">
			<div class="title">
				<label><input type="radio" name="contact[character]" value="自然人" onchange="post('character','自然人')" />自然人</label>
				<label><input type="radio" name="contact[character]" value="单位" onchange="post('character','单位')" checked="checked" />单位</label> (先选择，再输入下方数据)
			 </div>
		</div>

		<div class="item">
			<div class="title"><label>名称：</label></div>
			<input type="text" name="contact[name]" value="<?=$this->value('contact/name'); ?>" />
		</div>

		<div class="item">
			<div class="title"><label>分类：</label></div>
			<select name="contact[type]" class="right" style="width:49%">
				<?=options($this->value('contact/classification'),$this->value('contact/type'))?>
			</select>
			<select name="contact[classification]" style="width:50%">
				<?=options(array('_ENUM','client','classification'),$this->value('contact/classification'))?>
			</select>
			</select>
		</div>

		<div class="item">
			<div class="title"><label>简称：</label></div>
			<input name="contact[abbreviation]" value="<?=$this->value('contact/abbreviation'); ?>" type="text" />
		</div>

		<div class="item">
			<div class="title"><label>联系方式</label><label id="contactContactAdd"><? if($this->value('contact_contact_extra/show_add_form'))echo '-';else echo '+'?></label></div>

			<?=contact_contact?>
			<div id="contactContactAddForm" <? if(!$this->value('contact_contact_extra/show_add_form'))echo 'style="display:none"';?>>
				<select name="contact_contact[type]" style="width:30%">
					<?=options(array('_ENUM','client_contact','type'),$this->value('contact_contact/type'))?>
				</select>
				<input type="text" name="contact_contact[content]" value="<?=$this->value('contact_contact/content')?>" style="width:30%" />
				<input type="text" name="contact_contact[comment]" value="<?=$this->value('contact_contact/comment')?>" style="width:30%" />

				<input type="submit" name="submit[contact_contact]" value="添加" />
			</div>
		 </div>

		<div class="item">
			<div class="title"><label>相关人</label><label id="contactRelatedAdd"><? if($this->value('contact_related_extra/show_add_form'))echo '-';else echo '+'?></label></div>

			<?=$contact_related?>

			<div id="contactRelatedAddForm" <? if(!$this->value('contact_related_extra/show_add_form'))echo 'style="display:none"';?>>
				<label>名称：<input type="text" name="contact_related_extra[name]" value="<?=$this->value('contact_related_extra/name')?>" style="width:20%" /></label>

				<label>关系：</label>
				<select name="contact_related[role]" style="width:13%">
					<?=options(array('负责人','法务','财务','人事','行政','其他','其他代理人'),$this->value('contact_related/role'))?>
				</select>
				<input type="submit" name="submit[contact_related]" value="添加" />

				<br />
				<?=checkbox('单位','contact_related_extra[character]',$this->value('contact_related_extra/character'),'单位')?>

				<label>电话：<input type="text" name="contact_related_extra[phone]" value="<?=$this->value('contact_related_extra/phone')?>" style="width:20%" /></label>
				<label>电邮：<input type="text" name="contact_related_extra[email]" value="<?=$this->value('contact_related_extra/email')?>" style="width:20%" /></label>
			</div>
		 </div>

		<div class="item">
			<div class="title"><label>相关案件</label></div>

			<?=$contact_case?>
		 </div>

		<div class="item">
			<div class="title"><label>备注：</label></div>
			<textarea class="item" name="contact[comment]"><?=$this->value('contact/comment'); ?></textarea>
		</div>

		<div class="submit">
			<input class="submit" type="submit" name="submit[contact]" value="保存">
			<input class="submit" type="submit" name="submit[cancel]" value="关闭">
		</div>
	</div>
</div>
</form>