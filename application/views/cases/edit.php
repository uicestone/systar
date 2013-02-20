<form method="post" name="<?=CONTROLLER?>" id="<?=$this->cases->id?>" enctype="multipart/form-data" encoding="multipart/form-data">
<div class="contentTableMenu">
	<div class="right">
<? if($responsible_partner==$this->user->id && !$cases['is_reviewed'] && !$cases['is_query']){?>
		<button type="submit" name="submit[review]">立案审核</button>
<? }//TODO: 批量替换多余的空格?>
<? if($responsible_partner!=$this->user->id && !$cases['client_lock'] && $cases['is_reviewed']){?>
		<button type="submit" name="submit[apply_lock]">申请锁定</button>
<? }?>
<? if($this->user->isLogged('finance') && $this->value('cases/apply_file') && !$this->value('cases/finance_review')){?>
		<button type="submit" name="submit[review_finance]">财务审核</button>
<? }?>
<? if($this->user->isLogged('admin') && $this->value('cases/apply_file') && !$this->value('cases/info_review')){?>
		<button type="submit" name="submit[review_info]">信息审核</button>
<? }?>
<? if($this->user->isLogged('manager') && $this->value('cases/apply_file') && !$this->value('cases/manager_review')){?>
		<button type="submit" name="submit[review_manager]">主管审核</button>
<? }?>
<? if($this->user->isLogged('admin') && $this->value('cases/apply_file') && $this->value('cases/finance_review') && $this->value('cases/info_review') && $this->value('cases/manager_review') && !$this->value('cases/filed')){?>
		<button type="submit" name="submit[file]">实体归档</button>
<? }?>
<? if($cases['is_query']){ ?>
		<button type="submit" name="submit[new_case]">立案</button>
		<button type="submit" name="submit[file]">归档</button>
<? } ?>
<? if(!$cases['apply_file'] &&
	$cases['is_reviewed'] && 
	$cases['type_lock'] && 
	$cases['client_lock'] &&
	$cases['staff_lock'] &&
	$cases['fee_lock']
){?>
		<button type="submit" name="submit[apply_file]">申请归档</button>
<? }?>
		<button type="submit" name="submit[cases]">保存</button>
		<button type="submit" name="submit[cancel]">关闭</button>
	</div>
</div>

<div class="contentTableBox">
	<div class="contentTable">
		<div class="item">
			<div class="title"><label title="内部ID：<?=$this->value('cases/id')?>"><?=$this->value('cases/num');?></label></div>
	
<?if($this->value('cases/num')){?>
			<div class="field" id="case_name">
				<span class="right">
					<?=$case_status?>
				</span>
	
				<?=$this->value('cases/name')?>
				&nbsp;
			</div>
<?}?>
<?if($this->value('cases/classification')=='内部行政'){?>
			<span class="field">内部行政</span>
<?}else{?>
			<select id="type" name="labels[领域]"<?if($cases['type_lock']){?> disabled="disabled"<?}?>>
			<?=options($this->config->item('案件领域'),$this->value('labels/领域'),'领域');?>
			</select>
<?	if($cases['is_query']){ ?>
			<select id="classification" name="labels[咨询方式]">
			<?=options($this->config->item('咨询方式'),$this->value('labels/咨询方式'),'咨询方式');?>
			</select>
<?	}else{?>
			<select id="classification" name="labels[分类]"<?if($cases['type_lock']){?> disabled="disabled"<?}?>>
			<?=options(array('诉讼','非诉讼','法律顾问'),$this->value('labels/分类'),'分类');?>
			</select>
			<select id="stage" name="labels[阶段]">
			<?=options($case_type_array,$this->value('labels/阶段'),'阶段');?>
			</select>
<?	}?>
<?}?>
<?if($cases['is_query']){ ?>
			<input type="text" name="cases[first_contact]" value="<?=$this->value('cases/first_contact')?>" placeholder="首次接待日期" title="首次接待日期" class="date" />
<?}else{?>
			<input type="text" name="cases[time_contract]" value="<?=$this->value('cases/time_contract')?>" placeholder="立案日期" title="立案日期" class="date" <? if($cases['is_reviewed'])echo 'disabled';?> />
			-
			<input type="text" name="cases[time_end]" value="<?=$this->value('cases/time_end')?>" placeholder="预估结案日期" title="预估结案日期" class="date" <? if($cases['is_reviewed'])echo 'disabled';?> />
<?}?>
<?if(!$cases['num']){?>
			<button type="submit" name="submit[apply_case_num]">获得案号</button>
<?}else{?>
			<input type="text" name="cases[name_extra]" value="<?=$this->value('cases/name_extra')?>" placeholder="后缀" />
<?}?>
		</div>
	
		<div class="item" name="client">
			<div class="title"><label>客户及相关人：</label>
				<label class="toggle-add-form">+</label>
<? if($responsible_partner==$this->user->id && !$cases['client_lock'] && $cases['is_reviewed']){?>
				<button type="submit" name="submit[lock_client]">锁定</button>
<? }?>
<? if($responsible_partner==$this->user->id && $cases['client_lock']){ ?>
				<button type="submit" name="submit[unlock_client]">解锁</button>
<? } ?>
			</div>
		
			<?=$client_list?>
	
			<div class="add-form hidden">
				<input type="text" name="client[name]" value="<?=$this->value('client/name')?>" placeholder="名称" autocomplete-model="client" />
				<input type="text" name="case_client[client]" class="hidden" />
				
				<span display-for="new" class="hidden">
					<?=checkbox('单位','client[character]',$this->value('client/character'),'单位','disabled="disabled"')?>

					<select name="client[type]" disabled="disabled">
						<?=options($cases['client_lock']?array('联系人','相对方'):array('客户','相对方','联系人'),$this->value('case_client_extra/classification'));?>
					</select>

					<select name="client_labels[类型]" disabled="disabled"></select>
				
				</span>

				<span display-for="new non-client" class="hidden">
					<input type="text" name="client[work_for]" placeholder="工作单位" disabled="disabled" />
				</span>
	
				<select name="case_client[role]">
					<?=options(array('原告','被告','第三人','上诉人','被上诉人','申请人','被申请人','对方代理人','法官','检察官'),$this->value('case_client/role'),'本案地位',false,true);?>
				</select>
	
				<br display-for="new" class="hidden" />
				 
				<span display-for="new" class="hidden">
					<input type="text" name="client_profiles[电话]" value="<?=$this->value('client_profiles/电话');?>" placeholder="电话" disabled="disabled" />
					<input type="text" name="client_profiles[电子邮箱]" value="<?=$this->value('client_profiles/电子邮箱');?>" placeholder="电子邮件" disabled="disabled" />
				</span>
				
				<span display-for="new client" class="hidden">
					<label>来源：</label>
					<select name="client_source[type]" disabled="disabled">
						<?=options($this->config->item('客户来源类型'),$this->value('client_source/type'),'来源类型')?>
					</select>
					<input type="text" name="client_source[detail]" value="<?=$this->value('client_source/detail')?>" class="hidden" disabled="disabled" locked-by="case_client_extra[source_type]" />
					<input type="text" name="client[staff_name]" placeholder="来源律师" value="<?=$this->value('client/staff_name')?$this->value('client/staff_name'):$this->user->name?>" disabled="disabled" />
				</span>
				<button type="submit" name="submit[case_client]">添加</button>
			</div>
		 </div>
	
		<div class="item" name="staff">
			<div class="title"><label>律师：</label>
				<label class="toggle-add-form">＋</label>
<?if($responsible_partner==$this->user->id && !$cases['staff_lock'] && $cases['is_reviewed']){?>
				<button type="submit" name="submit[lock_lawyer]">锁定</button>
<? }?>
<? if($responsible_partner==$this->user->id && $cases['staff_lock']){ ?>
				<button type="submit" name="submit[unlock_lawyer]">解锁</button>
<? } ?>
			</div>
	
			<?=$staff_list?>
			
			<div class="add-form hidden">
				<input type="text" name="staff[name]" value="<?=$this->value('staff/name');?>" placeholder="姓名" autocomplete-model="staff" />
				<input name="staff[id]" class="hidden" />
				<select name="staff[role]">
					<?=options($staff_role_array,$this->value('staff/role'),'本案职务');?>
				</select>
				<input type="text" name="staff[actual_contribute]" value="<?=$this->value('staff/actual_contribute')?>" placeholder="%" class="hidden" />
				<button type="submit" name="submit[staff]">添加</button>
			</div>
		</div>
		
<? if($cases['is_query']){//咨询阶段显示报价情况，不显示律师费和办案费?>
		<div class="item">
			<div class="title"><label>报价：</label></div>
			<input type="text" name="cases[quote]" value="<?=$this->value('cases/quote') ?>" style="width:100%" />
		</div>
<? }?>
		<div class="item" name="fee">
			<div class="title">
				<label>签约律师费：</label>
				<label><input type="checkbox" name="cases[timing_fee]" value="1"<?if($this->value('cases/timing_fee')){?> checked="checked"<?}?><?if($cases['fee_lock']){?> disabled="disabled"<?}?>/>计时收费</label> 
				<label class="toggle-add-form">+</label>
				<label id="caseTimingFeeSave">
	
<? if($this->value('cases/timing_fee') && !isset($case_fee_timing_string)){?>
					<button type="submit" name="submit[case_fee_timing]">保存</button>
<? }?></label>
<? if(($responsible_partner==$this->user->id || $this->user->isLogged('finance')) && !$cases['fee_lock']){?>
				<button type="submit" name="submit[lock_fee]">锁定</button>
<? }?>
<? if(($responsible_partner==$this->user->id || $this->user->isLogged('finance')) && $cases['fee_lock']){ ?>
				<button type="submit" name="submit[unlock_fee]">解锁</button>
<? } ?>
				
<? if($this->user->isLogged('finance')){?>
				<button type="button" onclick="$.locationHash('account/add?case=<?=$this->value('cases/id')?>')">到账</button>
<? }?>
<? if($this->user->isLogged('finance')){?>
				<button type="submit" name="submit[case_fee_review]" disabled="disabled" class="hidden">忽略</button>
<? }?>
			</div>
	
			<div>
				<div class="timing-fee-detail<?if(!$this->value('cases/timing_fee')){?> hidden<?}?>">
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
<? if(!$cases['fee_lock']){?>
			<div class="add-form hidden">
				<select name="case_fee[type]">
					<?=options($cases['is_query']?array('咨询费'):array('固定','风险','计时预付'),$this->value('case_fee/type'),'类型');?>
				</select>
				<input type="text" name="case_fee[fee]" value="<?=$this->value('case_fee/fee');?>" placeholder="数额" />
				<input type="text" name="case_fee[condition]" value="<?=$this->value('case_fee/condition');?>" placeholder="付款条件" />
				<input type="text" name="case_fee[pay_date]" value="<?=$this->value('case_fee/pay_date')?>" placeholder="预估日期" class="date" />
				<button type="submit" name="submit[case_fee]">添加</button>
			</div>
<? }?>
		</div>
	
<?if(!$cases['is_query']){?>
		<div class="item" name="miscfee">
			<div class="title"><label>办案费约定情况：</label><label class="toggle-add-form">+</label></div>
	
			<?=$miscfee_list?>
			<div class="add-form hidden">
				<select name="case_fee_misc[receiver]">
					<?=options(array('承办律师','律所'),$this->value('case_fee_misc[receiver]'),'收款方');?>
				</select>
				<input type="text" name="case_fee_misc[fee]" value="<?=$this->value('case_fee_misc/fee');?>" placeholder="数额" />
				<input type="text" name="case_fee_misc[comment]" value="<?=$this->value('case_fee_misc/comment');?>" placeholder="付款条件" />
				<input type="text" name="case_fee_misc[pay_date]" value="<?=$this->value('case_fee_misc/pay_date')?>" placeholder="预估日期" class="date" />
				<button type="submit" name="submit[case_fee_misc]">添加</button>
			</div>
		</div>
<?}?>
	
		<div class="item" name="document">
			<div class="title"><label>文件：</label><label class="toggle-add-form">+</label>
<? if($this->value('cases/apply_file')){ ?>
				<button type="submit" name="submit[file_document_list]">下载目录</button>
<? } ?>
			</div>
	
			<?=$document_list?>

			<div class="add-form">
				<input type="file" name="document" id="file" width="30%" />
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
					<a href="#schedule/lists?case=<?=$this->value('cases/id')?>">所有日志>></a>
				</span>
				<label>最新日志：
					<a href="javascript:createSchedule(null,null,null,<?=$this->value('cases/id'),true?>)">添加>></a>
				</label>
			</div>
			<?=$schedule_list?>
		</div>
	
		<div class="item" name="plan">
			<div class="title">
				<span class="right">
					<a href="#schedule/plan?case=<? echo $this->value('cases/id')?>">所有计划>></a>
				</span>
				<label>日程计划：
					<a href="javascript:createSchedule(null,null,null,<?=$this->value('cases/id'),false?>)">添加>></a>
				</label>
			</div>
			<?$plan_list?>
		</div>
	
<? if(!$cases['is_query'] && !in_array('法律顾问',$labels)){?>
		<div class="item">
			<div class="title"><label>争议焦点：（案件标的）</label></div>
			<textarea class="item" name="cases[focus]" type="text" rows="2"><?=$this->value('cases/focus')?></textarea>
		</div>
<? }?>
	
		<div class="item">
			<div class="title"><label>案情简介：</label></div>
			<textarea class="item" name="cases[summary]" type="text" rows="4"><?=$this->value('cases/summary')?></textarea>
		</div>
	
		<div class="item">
			<div class="title"><label>备注：</label></div>
			<textarea class="item" name="cases[comment]" type="text" rows="3"><?=$this->value('cases/comment')?></textarea>
		</div>
	
		<div class="submit">
			<button type="submit" name="submit[cases]">保存</button>
			<button type="submit" name="submit[cancel]">关闭</button>
		</div>
	</div>
</div>
</form>
<?=javascript('case_add')?>
