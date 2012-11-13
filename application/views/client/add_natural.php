<? javascript('client_add')?>
<form method="post">
<div class="contentTableMenu">
	<div class="right">
		<input type="submit" name="submit[client]" value="保存" />
		<input type="submit" name="submit[cancel]" value="关闭" />
	</div>
</div>
<div class="contentTableBox">
	<div class="contentTable">
		<div class="item">
			<div class="title">
				<label><input type="radio" name="client[character]" value="自然人" onchange="post('character','自然人')" checked="checked" />自然人</label>
				<label><input type="radio" name="client[character]" value="单位" onchange="post('character','单位')" />单位</label> (先选择，再输入下方数据)
			</div>
		</div>

		<div class="item">
			<div class="title"><label>姓名：</label></div>
			<input name="client[name_en]" value="<?=post('client/name_en'); ?>" type="text" placeholder="英文" class="right" style="width:49%" />
			<input name="client[name]" value="<?=post('client/name'); ?>" type="text" placeholder="中文" style="width:49%" />
			<br />
			<input type="text" name="client[birthday]" value="<?=post('client/birthday'); ?>" placeholder="生日" class="date right" style="width:49%" />
			<input type="text" name="client[id_card]" value="<?=post('client/id_card'); ?>" placeholder="身份证" style="width:49%" />
		</div>

		<div class="item">
			<div class="title"><label>分类：</label></div>
			<select name="client[type]" class="right" style="width:49%">
				<? displayOption(post('client/classification'),post('client/type'))?>
			</select>
			<select name="client[classification]" style="width:50%">
				<? displayOption(array('_ENUM','client','classification'),post('client/classification'))?>
			</select>
		</div>

		<div class="item">
			<div class="title"><label>来源：</label></div>
			<select name="source[type]" style="width:30%">
				<? displayOption(array('_ENUM','client_source','type'),post('source/type'))?>
			</select>
			<input type="text" name="source[detail]" value="<?=post('source/detail')?>" style="width:30%" <? if(!in_array(post('source/type'),array('其他网络','媒体','老客户介绍','合作单位介绍','其他')))echo 'disabled="disabled"';?> />
			来源律师：<input type="text" name="client_extra[source_lawyer_name]" style="width:20%" value="<?=post('client_extra/source_lawyer_name')?>" />
		</div>

		<div class="item">
			<div class="title"><label>性别：</label></div>
			<? displayRadio(array('男','女'),'client[gender]',post('client/gender'))?>
		</div>

		<div class="item">
			<div class="title"><label>联系方式</label><label id="clientContactAdd"><? if(post('client_contact_extra/show_add_form'))echo '-';else echo '+'?></label></div>
			<?=$contact_table?>
			<div id="clientContactAddForm" <? if(!post('client_contact_extra/show_add_form'))echo 'style="display:none"';?>>
				<select name="client_contact[type]" style="width:30%">
					<? displayOption(array('_ENUM','client_contact','type'),post('client_contact/type'))?>
				</select>
				<input type="text" name="client_contact[content]" value="<?=post('client_contact/content')?>" style="width:30%" />
				<input type="text" name="client_contact[comment]" value="<?=post('client_contact/comment')?>" style="width:30%" />

				<input type="submit" name="submit[client_contact]" value="添加" />
			</div>
		 </div>

		<div class="item">
			<div class="title"><label>相关人</label><label id="clientClientAdd"><? echo post('client_client_extra/show_add_form')?'-':'+'?></label></div>
			<?=$client_table?>
			<div id="clientClientAddForm" <? if(post('client_client_extra/show_add_form'))echo 'style="display:block"';?>>
				<input type="text" name="client_client_extra[name]" value="<?=post('client_client_extra/name')?>" placeholder="名称" autocomplete="client" autocomplete-input-name="client_client[client_right]" style="width:20%" />

				<select name="client_client[role]" style="width:13%">
					<? displayOption(array('父','母',(post('client/gender')=='男'?'妻':'夫'),'亲属','朋友','其他','代理人'),post('client_client/role'))?>
				</select>
				<span class="autocomplete-no-result-menu">
					<? displayCheckbox('单位','client_client_extra[character]',post('client_client_extra/character'),'单位')?>
		
					<input type="text" name="client_client_extra[phone]" value="<?=post('client_client_extra/phone')?>" placeholder="电话" style="width:20%" />
					<input type="text" name="client_client_extra[email]" value="<?=post('client_client_extra/email')?>" placeholder="电邮" style="width:20%" />
				</span>
				<input type="submit" name="submit[client_client]" value="添加" />
			</div>
		 </div>

		<div class="item">
			<div class="title"><label>单位：</label></div>
			<input type="text" name="client[work_for]" value="<?=post('client/work_for'); ?>" />
		</div>

		<div class="item">
			<div class="title"><label>职位：</label></div>
			<input type="text" name="client[position]" value="<?=post('client/position'); ?>" />
		</div>

		<div class="item">
			<div class="title"><label>相关案件</label></div>
			<?=$case_table?>
		 </div>

		<div class="item">
			<div class="title"><label>备注：</label></div>
			<textarea name="client[comment]"><?=post('client/comment')?></textarea>
		</div>

		<div class="submit">
			<input type="submit" name="submit[client]" value="保存" />
			<input type="submit" name="submit[cancel]" value="关闭" />
		</div>
	</div>
</div>
</form>