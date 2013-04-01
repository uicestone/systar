<form method="post" name="<?=CONTROLLER?>" id="<?=$this->project->id?>" enctype="multipart/form-data" encoding="multipart/form-data">
<div class="contentTableBox">
	<div class="item">
		<div class="title">
			<span class="right">
				<?//TODO project_label?>
			</span>
			<label title="内部ID：<?=$this->value('project/id')?>"><?=$this->value('project/num');?></label>
		</div>

		<select id="type" name="labels[领域]"<?if(in_array('类型已锁定',$labels)){?> disabled="disabled"<?}?>>
		<?=options($this->config->item('案件领域'),$this->value('labels/领域'),'领域');?>
		</select>
<?if(in_array('咨询',$labels)){ ?>
		<select id="classification" name="labels[咨询方式]">
		<?=options($this->config->item('咨询方式'),$this->value('labels/咨询方式'),'咨询方式');?>
		</select>
<?}else{?>
		<select id="classification" name="labels[分类]"<?if(in_array('类型已锁定',$labels)){?> disabled="disabled"<?}?>>
		<?=options(array('诉讼','非诉讼','法律顾问'),$this->value('labels/分类'),'分类');?>
		</select>
		<select name="labels[阶段]"<?if(!isset($labels['分类']) || $labels['分类']!='诉讼'){?> class="hidden" disabled="disabled"<?}?>>
		<?=options($case_type_array,$this->value('labels/阶段'),'阶段');?>
		</select>
		<input type="text" name="project[name]" value="<?=$this->value('project/name')?>" placeholder="案件名称" style="width:300px;">
<?	if(!$project['num']){?>
		<button type="submit" name="submit[apply_project_num]" class="major">获得案号</button>
<?	}?>
<?}?>
<?if(in_array('咨询',$labels)){ ?>
		<input type="text" name="project[first_contact]" value="<?=$this->value('project/first_contact')?>" placeholder="首次接待日期" title="首次接待日期" class="date" />
<?}else{?>
		<input type="text" name="project[time_contract]" value="<?=$this->value('project/time_contract')?>" placeholder="立案日期" title="立案日期" class="date" <? if(in_array('在办',$labels))echo 'disabled';?> />
		-
		<input type="text" name="project[time_end]" value="<?=$this->value('project/time_end')?>" placeholder="预估结案日期" title="预估结案日期" class="date" <? if(in_array('在办',$labels))echo 'disabled';?> />
<?}?>
	</div>

	<div class="item" name="client">
		<div class="title"><label>客户及相关人：</label>
<? if($responsible_partner==$this->user->id && !in_array('客户已锁定',$labels) && in_array('在办', $labels)){?>
			<button type="submit" name="submit[lock_client]">锁定</button>
<? }?>
<? if($responsible_partner==$this->user->id && in_array('客户已锁定',$labels)){ ?>
			<button type="submit" name="submit[unlock_client]">解锁</button>
<? } ?>
		</div>

		<?=$client_list?>

		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="text" name="client[name]" value="<?=$this->value('client/name')?>" placeholder="名称" autocomplete-model="client" />
			<input type="text" name="case_client[client]" class="hidden" />

			<select name="case_client[role]">
				<?=options(array('原告','被告','第三人','上诉人','被上诉人','申请人','被申请人','对方代理人','法官','检察官'),$this->value('case_client/role'),'本案地位',false,true);?>
			</select>

			<span display-for="new" class="hidden">
				<?=checkbox('单位','client[character]',$this->value('client/character'),'单位','disabled="disabled"')?>

				<select name="client[type]" disabled="disabled">
					<?=options(in_array('客户已锁定',$labels)?array('联系人'):array('客户','联系人'),$this->value('client/type'),'人员类型');?>
				</select>

				<select name="client_labels[类型]" disabled="disabled">
					<?=options($this->label->getRelatives($this->value('client/type')),$this->value('client_labels/类型'),$this->value('client/type').'类型');?>
				</select>

			</span>

			<br display-for="new" class="hidden" />

			<span display-for="new" class="hidden">
				<input type="text" name="client_profiles[电话]" value="<?=$this->value('client_profiles/电话');?>" placeholder="电话" disabled="disabled" />
				<input type="text" name="client_profiles[电子邮件]" value="<?=$this->value('client_profiles/电子邮件');?>" placeholder="电子邮件" disabled="disabled" />
				<input type="text" name="client[work_for]" placeholder="工作单位" disabled="disabled" />
			</span>

			<span display-for="new client" class="hidden">
				<label>来源：</label>
				<select name="client_profiles[来源类型]" disabled="disabled">
					<?=options($this->config->item('客户来源类型'),$this->value('client_profiles/来源类型'),'来源类型')?>
				</select>
				<input type="text" name="client_profiles[来源]" value="<?=$this->value('client_profiles/来源')?>" class="hidden" disabled="disabled" locked-by="client_profiles[来源类型]" />
				<input type="text" name="client[staff_name]" placeholder="来源律师" value="<?=$this->value('client/staff_name')?$this->value('client/staff_name'):$this->user->name?>" disabled="disabled" />
			</span>
			<button type="submit" name="submit[case_client]">添加</button>
		</span>
	 </div>

<? if(isset($labels['分类']) && in_array($labels['分类'],array('诉讼','非诉讼'))){?>
	<div class="item">
		<div class="title"><label>争议焦点：（案件标的）</label></div>
		<input name="project[focus]" type="text" value="<?=$this->value('project/focus')?>" style="width:99%;font-size:1.2em;" />
	</div>
<? }?>

	<div class="item" name="staff">
		<div class="title"><label>律师：</label>
<?if($responsible_partner==$this->user->id && !in_array('职员已锁定',$labels) && in_array('在办', $labels)){?>
			<button type="submit" name="submit[lock_staff]">锁定</button>
<? }?>
<? if($responsible_partner==$this->user->id && in_array('职员已锁定',$labels)){ ?>
			<button type="submit" name="submit[unlock_staff]">解锁</button>
<? } ?>
		</div>

		<?=$staff_list?>

		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="text" name="staff[name]" value="<?=$this->value('staff/name');?>" placeholder="姓名" autocomplete-model="staff" />
			<input name="staff[id]" class="hidden" />
			<select name="staff[role]">
				<?=options($staff_role_array,$this->value('staff/role'),'本案职务');?>
			</select>
			<input type="text" name="staff[actual_contribute]" value="<?=$this->value('staff/actual_contribute')?>" placeholder="%" class="hidden" />
			<button type="submit" name="submit[staff]">添加</button>
		</span>
	</div>

<? if(in_array('咨询',$labels)){//咨询阶段显示报价情况，不显示律师费和办案费?>
	<div class="item">
		<div class="title"><label>报价：</label></div>
		<input type="text" name="project[quote]" value="<?=$this->value('project/quote') ?>" style="width:99%" />
	</div>
<? }?>
	<div class="item" name="fee">
		<div class="title">
			<label>签约律师费：</label>
			<label><input type="checkbox" name="project[timing_fee]" value="1"<?if($this->value('project/timing_fee')){?> checked="checked"<?}?><?if(in_array('费用已锁定',$labels)){?> disabled="disabled"<?}?>/>计时收费</label> 
			<label id="caseTimingFeeSave">

<? if($this->value('project/timing_fee') && !isset($case_fee_timing_string)){?>
				<button type="submit" name="submit[case_fee_timing]">保存</button>
<? }?></label>
<? if(($responsible_partner==$this->user->id || $this->user->isLogged('finance')) && !in_array('费用已锁定',$labels)){?>
			<button type="submit" name="submit[lock_fee]">锁定</button>
<? }?>
<? if(($responsible_partner==$this->user->id || $this->user->isLogged('finance')) && in_array('费用已锁定',$labels)){ ?>
			<button type="submit" name="submit[unlock_fee]">解锁</button>
<? } ?>

<? if($this->user->isLogged('finance')){?>
			<button type="button" onclick="window.location.href='account/add?project=<?=$this->value('project/id')?>'">到账</button>
<? }?>
<? if($this->user->isLogged('finance')){?>
			<button type="submit" name="submit[case_fee_review]" disabled="disabled" class="hidden">忽略</button>
<? }?>
		</div>

		<div>
			<div class="timing-fee-detail<?if(!$this->value('project/timing_fee')){?> hidden<?}?>">
<?if(isset($case_fee_timing_string) && $case_fee_timing_string!=''){?>
				<?=$case_fee_timing_string?>
<?}else{?>
				包含：<input type="text" name="case_fee_timing[included_hours]" value="<?=$this->value('case_fee_timing/included_hours');?>" style="width:30px" />小时&nbsp;
				账单起始日：<input type="text" name="case_fee_timing[date_start]" value="<?=$this->value('case_fee_timing/date_start')?>" class="date" />&nbsp;
				账单日：<input type="text" name="case_fee_timing[bill_day]" value="<?=$this->value('case_fee_timing/bill_day')?>" style="width:30px;" />日&nbsp;
				付款日：<input type="text" name="case_fee_timing[payment_day]" value="<?=$this->value('case_fee_timing/payment_day');?>" style="width:30px;" />日&nbsp;
				付款周期：<input type="text" name="case_fee_timing[payment_cycle]" value="<?=$this->value('case_fee_timing/payment_cycle');?>" style="width:30px;" />个月&nbsp;
				合同周期：<input type="text" name="case_fee_timing[contract_cycle]" value="<?=$this->value('case_fee_timing/contract_cycle');?>" style="width:30px;" />个月&nbsp;
<? }?>
			</div>
		</div>

		<?=$fee_list?>	
		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<select name="project_account[type]">
				<?=options(in_array('咨询',$labels)?array('咨询费'):array('固定','风险','计时预付'),$this->value('project_account/type'),'类型');?>
			</select>
			<input type="text" name="project_account[fee]" value="<?=$this->value('project_account/fee');?>" placeholder="数额" />
			<input type="text" name="project_account[condition]" value="<?=$this->value('project_account/condition');?>" placeholder="付款条件" />
			<input type="text" name="project_account[pay_date]" value="<?=$this->value('project_account/pay_date')?>" placeholder="预估日期" class="date" />
			<button type="submit" name="submit[project_account]">添加</button>
		</span>
	</div>

<?if(!in_array('咨询',$labels)){?>
	<div class="item" name="miscfee">
		<div class="title"><label>办案费约定情况：</label></div>

		<?=$miscfee_list?>
		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<select name="case_fee_misc[receiver]">
				<?=options(array('承办律师','律所'),$this->value('case_fee_misc[receiver]'),'收款方');?>
			</select>
			<input type="text" name="case_fee_misc[fee]" value="<?=$this->value('case_fee_misc/fee');?>" placeholder="数额" />
			<input type="text" name="case_fee_misc[comment]" value="<?=$this->value('case_fee_misc/comment');?>" placeholder="备注" />
			<input type="text" name="case_fee_misc[pay_date]" value="<?=$this->value('case_fee_misc/pay_date')?>" placeholder="预估日期" class="date" />
			<button type="submit" name="submit[case_fee_misc]">添加</button>
		</span>
	</div>
<?}?>

	<div class="item" name="document">
		<div class="title"><label>文件：</label>
<? if($this->value('project/apply_file')){ ?>
			<button type="submit" name="submit[file_document_list]">下载目录</button>
<? } ?>
		</div>

		<?=$document_list?>

		<div class="add-form">
			<input type="file" name="document" id="file" data-url="/document/submit" width="30%" />
			<select name="document_labels[类型]">
			<?=options($this->config->item('案件文档类型'),$this->value('document_labels/类型'),'类型');?>
			</select>
			<input type="text" name="document[comment]" placeholder="具体文件名称" />
			<button type="submit" name="submit[case_document]">上传</button>
		</div>
	</div>

	<div class="item" name="schedule">
		<div class="title">
			<span class="right">
				<?=$schedule_time?>小时
				<a href="#schedule/lists?case=<?=$this->value('project/id')?>">所有日志>></a>
			</span>
			<label>最新日志：
				<a href="javascript:$.createSchedule({project:<?=$this->value('project/id')?>})">添加>></a>
			</label>
		</div>
		<?=$schedule_list?>
	</div>

	<div class="item" name="plan">
		<div class="title">
			<span class="right">
				<a href="#schedule/plan?case=<? echo $this->value('project/id')?>">所有计划>></a>
			</span>
			<label>日程计划：
				<a href="javascript:createSchedule(null,null,null,<?=$this->value('project/id'),false?>)">添加>></a>
			</label>
		</div>
		<?$plan_list?>
	</div>

	<div class="item">
		<div class="title"><label>案情简介：</label></div>
		<textarea class="item" name="project[summary]" type="text" rows="4"><?=$this->value('project/summary')?></textarea>
	</div>

	<div class="item">
		<div class="title"><label>备注：</label></div>
		<textarea class="item" name="project[comment]" type="text" rows="3"><?=$this->value('project/comment')?></textarea>
	</div>
</div>
</form>
<?=javascript('project_add')?>
