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
				<label><input type="radio" name="client[character]" value="自然人" onchange="post('character','自然人')" />自然人</label>
				<label><input type="radio" name="client[character]" value="单位" onchange="post('character','单位')" checked="checked" />单位</label> (先选择，再输入下方数据)
			 </div>
		</div>

		<div class="item">
			<div class="title"><label>名称：</label></div>
			<input type="text" name="client[abbreviation]" value="<? displayPost('client/abbreviation')?>" class="right" style="width:28%" />
			<input type="text" name="client[name]" value="<? displayPost('client/name')?>" style="width:70%" />
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
			<input type="text" name="source[detail]" value="<? displayPost('source/detail')?>" style="width:30%" <? if(!in_array(post('source/type'),array('其他网络','媒体','老客户介绍','合作单位介绍','其他')))echo 'disabled="disabled"';?> />
			<label>来源律师：<input type="text" name="client_extra[source_lawyer_name]" style="width:20%;" value="<? displayPost('client_extra/source_lawyer_name')?>" /></label>
		</div>

		<div class="item">
			<div class="title"><label>联系方式</label><label id="clientContactAdd"><? if(post('client_contact_extra/show_add_form'))echo '-';else echo '+'?></label></div>

			<?$contacts->generate()?>
			<div id="clientContactAddForm" <? if(!post('client_contact_extra/show_add_form'))echo 'style="display:none"';?>>
				<select name="client_contact[type]" style="width:30%">
					<? displayOption(array('_ENUM','client_contact','type'),post('client_contact/type'))?>
				</select>
				<input type="text" name="client_contact[content]" value="<? displayPost('client_contact/content')?>" style="width:30%" />
				<input type="text" name="client_contact[comment]" value="<? displayPost('client_contact/comment')?>" style="width:30%" />

				<input type="submit" name="submit[client_contact]" value="添加" />
			</div>
		 </div>

		<div class="item">
			<div class="title"><label>相关人</label><label id="clientClientAdd"><? echo post('client_client_extra/show_add_form')?'-':'+'?></label></div>

			<?$related_clients->generate()?>

			<div id="clientClientAddForm" <? if(!post('client_client_extra/show_add_form'))echo 'style="display:none"';?>>
				<input type="text" name="client_client_extra[name]" value="<? displayPost('client_client_extra/name')?>" placeholder="名称" autocomplete="client" autocomplete-input-name="client_client[client_right]" style="width:20%" />

				<select name="client_client[role]" style="width:13%">
					<? displayOption(array('负责人','法务','财务','人事','行政','其他','其他代理人'),post('client_client/role'))?>
				</select>
				<span class="autocomplete-no-result-menu">
					<? displayCheckbox('单位','client_client_extra[character]',post('client_client_extra/character'),'单位')?>
		
					<input type="text" name="client_client_extra[phone]" value="<? displayPost('client_client_extra/phone')?>" placeholder="电话" style="width:20%" />
					<input type="text" name="client_client_extra[email]" value="<? displayPost('client_client_extra/email')?>" placeholder="电邮" style="width:20%" />
				</span>
				<input type="submit" name="submit[client_client]" value="添加" />
			</div>
		 </div>

		<div class="item">
			<div class="title"><label>相关案件</label></div>

			<?$cases->generate()?>
		 </div>

		<div class="item">
			<div class="title"><label>备注：</label></div>
			<textarea class="item" name="client[comment]"><?=post('client/comment'); ?></textarea>
		</div>

		<div class="submit">
			<input class="submit" type="submit" name="submit[client]" value="保存">
			<input class="submit" type="submit" name="submit[cancel]" value="关闭">
		</div>
	</div>
</div>
</form>