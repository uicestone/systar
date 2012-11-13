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
				<label><input type="radio" name="contact[character]" value="自然人" onchange="post('character','自然人')" checked="checked" />自然人</label>
				<label><input type="radio" name="contact[character]" value="单位" onchange="post('character','单位')" />单位</label> (先选择，再输入下方数据)
			</div>
		</div>

		<div class="item">
			<div class="title"><label>姓名：</label></div>
			<label>中文：<input name="contact[name]" value="<?=post('contact/name'); ?>" type="text" style="width:43%" /></label>
			<label>英文：<input name="contact[name_en]" value="<?=post('contact/name_en'); ?>" type="text" style="width:43%" /></label>
		</div>

		<div class="item">
			<div class="title"><label>分类：</label></div>
			<select name="contact[type]" class="right" style="width:49%">
				<? displayOption(post('contact/classification'),post('contact/type'))?>
			</select>
			<select name="contact[classification]" style="width:50%">
				<? displayOption(array('_ENUM','client','classification'),post('contact/classification'))?>
			</select>
			</select>
		</div>

		<div class="item">
			<div class="title"><label>性别：</label></div>
			<? displayRadio(array('男','女'),'contact[gender]',post('contact/gender'))?>
		</div>

		<div class="item">
			<div class="title"><label>联系方式</label><label id="contactContactAdd"><? if(post('contact_contact_extra/show_add_form'))echo '-';else echo '+'?></label></div>

			<?=$contact_contact?>
			<div id="contactContactAddForm" <? if(!post('contact_contact_extra/show_add_form'))echo 'style="display:none"';?>>
				<select name="contact_contact[type]" style="width:30%">
					<? displayOption(array('_ENUM','client_contact','type'),post('contact_contact/type'))?>
				</select>
				<input type="text" name="contact_contact[content]" value="<?=post('contact_contact/content')?>" style="width:30%" />
				<input type="text" name="contact_contact[comment]" value="<?=post('contact_contact/comment')?>" style="width:30%" />

				<input type="submit" name="submit[contact_contact]" value="添加" />
			</div>
		 </div>

		<div class="item">
			<div class="title"><label>相关人</label><label id="contactRelatedAdd"><? if(post('contact_related_extra/show_add_form'))echo '-';else echo '+'?></label></div>

			<?=$contact_related?>
			
			<div id="contactRelatedAddForm" <? if(!post('contact_related_extra/show_add_form'))echo 'style="display:none"';?>>
				<label>名称：<input type="text" name="contact_related_extra[name]" value="<?=post('contact_related_extra/name')?>" style="width:20%" /></label>

				<label>关系：</label>
				<select name="contact_related[role]" style="width:13%">
					<? displayOption(array('父','母',(post('contact/gender')=='男'?'妻':'夫'),'亲属','朋友','其他','代理人'),post('contact_related/role'))?>
				</select>
				<input type="submit" name="submit[contact_related]" value="添加" />

				<br />
				<? displayCheckbox('单位','contact_related_extra[character]',post('contact_related_extra/character'),'单位')?>

				<label>电话：<input type="text" name="contact_related_extra[phone]" value="<?=post('contact_related_extra/phone')?>" style="width:20%" /></label>
				<label>电邮：<input type="text" name="contact_related_extra[email]" value="<?=post('contact_related_extra/email')?>" style="width:20%" /></label>
			</div>
		 </div>

		<div class="item">
			<div class="title"><label>单位与职务：</label></div>
			<input type="text" name="contact[position]" placeholder="职务" value="<?=post('contact/position')?>" class="right" style="width:49%" />
			<input type="text" name="contact[work_for]" placeholder="单位" value="<?=post('contact/work_for')?>" style="width:49%" />
		</div>

		<div class="item">
			<div class="title"><label>相关案件</label></div>

			<?=$contact_case?>
		 </div>

		<div class="item">
			<div class="title"><label>备注：</label></div>
			<textarea name="contact[comment]"><?=post('contact/comment'); ?></textarea>
		</div>

		<div class="submit">
			<input type="submit" name="submit[contact]" value="保存" />
			<input type="submit" name="submit[cancel]" value="关闭" />
		</div>
	</div>
</div>
</form>