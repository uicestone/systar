<form method="post" name="<?=CONTROLLER?>" id="<?=$this->cases->id?>" enctype="multipart/form-data">
<div class="contentTableMenu">
	<div class="right">
		<input type="submit" name="submit[cases]" value="保存" />
<? if($responsible_partner==$this->user->id && !$case['is_reviewed'] && !$case['is_query']){?>
		<button type="button" name="submit[review]">立案审核</button>
<? }?>
<? if($responsible_partner!=$this->user->id && !$case['client_lock'] && $case['is_reviewed']){?>
		<input type="submit" name="submit[apply_lock]" value="申请锁定" />
<? }?>
<? if($this->user->isLogged('finance') && $this->value('cases/apply_file') && !$this->value('cases/finance_review')){?>
		<input type="submit" name="submit[review_finance]" value="财务审核" />
<? }?>
<? if($this->user->isLogged('admin') && $this->value('cases/apply_file') && !$this->value('cases/info_review')){?>
		<input type="submit" name="submit[review_info]" value="信息审核" />
<? }?>
<? if($this->user->isLogged('manager') && $this->value('cases/apply_file') && !$this->value('cases/manager_review')){?>
		<input type="submit" name="submit[review_manager]" value="主管审核" />
<? }?>
<? if($this->user->isLogged('admin') && $this->value('cases/apply_file') && $this->value('cases/finance_review') && $this->value('cases/info_review') && $this->value('cases/manager_review') && !$this->value('cases/filed')){?>
		<input type="submit" name="submit[file]" value="实体归档" />
<? }?>
<? if($case['is_query']){ ?>
		<input type="submit" name="submit[new_case]" value="立案" />
		<input type="submit" name="submit[file]" value="归档" />
<? } ?>
<? if(!$case['apply_file'] &&
	$case['is_reviewed'] && 
	$case['type_lock'] && 
	$case['client_lock'] &&
	$case['lawyer_lock'] &&
	$case['fee_lock']
){?>
		<input type="submit" name="submit[apply_file]" value="申请归档" />
<? }?>
		<input type="submit" name="submit[cancel]" value="取消" />
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
			<select id="type" style="width:7%;" name="cases[type]" <? if($case['type_lock'])echo 'disabled';?>>
			<?=options(array('公司','劳动','房产','婚姻','继承','刑事','知产','留学','移民','行政','合同','侵权'),$this->value('cases/type'));?>
			</select>
<?	if($case['is_query']){ ?>
			<select id="classification" style="width:15%;" name="cases[query_type]" <? if($case['type_lock'])echo 'disabled';?>>
			<?=options(array('_ENUM','case','query_type'),$this->value('cases/query_type'));?>
			</select>
<?	}else{?>
			<select id="classification" style="width:15%;" name="cases[classification]" <? if($case['type_lock'])echo 'disabled';?>>
			<?=options(array('诉讼','非诉讼','法律顾问'),$this->value('cases/classification'));?>
			</select>
			<select id="stage" style="width:15%;" name="cases[stage]" <? if($case['type_lock'])echo 'disabled';?>>
			<?=options($case_type_array,$this->value('cases/stage'));?>
			</select>
<?	}?>
<?}?>
<?if($case['is_query']){ ?>
			<input type="text" name="cases[first_contact]" value="<?=$this->value('cases/first_contact')?>" placeholder="首次接待日期" title="首次接待日期" class="date" style="width:100px" />
<?}else{?>
			<input type="text" name="cases[time_contract]" value="<?=$this->value('cases/time_contract')?>" placeholder="立案日期" title="立案日期" class="date" style="width:100px" <? if($case['is_reviewed'])echo 'disabled';?> />
			-
			<input type="text" name="cases[time_end]" value="<?=$this->value('cases/time_end')?>" placeholder="预估结案日期" title="预估结案日期" class="date" style="width:100px" <? if($case['is_reviewed'])echo 'disabled';?> />
<?}?>
<?if(!$case['num']){?>
			<input type="submit" name="submit[apply_case_num]" value="获得案号" />
<?}else{?>
			<input type="text" name="cases[name_extra]" style="width:20%" value="<?=$this->value('cases/name_extra')?>" placeholder="后缀" />
<?}?>
		</div>
	
		<div class="item" name="client">
			<div class="title"><label>客户及相关人：</label>
				<label class="toggle-add-form">+</label>
<? if($responsible_partner==$this->user->id && !$case['client_lock'] && $case['is_reviewed']){?>
				<input type="submit" name="submit[lock_client]" value="锁定" />
<? }?>
<? if($responsible_partner==$this->user->id && $case['client_lock']){ ?>
				<input type="submit" name="submit[unlock_client]" value="解锁" />
<? } ?>
			</div>
		
			<?=$client_list?>
	
			<div class="add-form hidden">
				<input type="text" name="case_client_extra[name]" value="<?=$this->value('case_client_extra/name')?>" placeholder="名称" autocomplete-model="client" style="width:20%" />
				<input type="text" name="case_client[client]" class="hidden" />
				
				<?=checkbox('单位','case_client_extra[character]',$this->value('case_client_extra/character'),'单位','display-for="new"')?>

				<select name="case_client_extra[classification]" display-for="new" style="width:15%">
					<?=options($case['client_lock']?array('联系人','相对方'):array('客户','相对方','联系人'),$this->value('case_client_extra/classification'));?>
				</select>

				<select name="case_client_extra[type]" display-for="new non-opposite" style="width:15%"></select>
				
				<input type="text" name="case_client_extra[work_for]" placeholder="工作单位" display-for="non-client" style="width:10%" />
	
				<label>本案地位：</label>
				<select name="case_client[role]" placeholder="本案地位" style="width:15%">
					<?=options(array('原告','被告','第三人','上诉人','被上诉人','申请人','被申请人','对方代理人','法官','检察官','其他'),$this->value('case_client/role'));?>
				</select>
	
				<br display-for="new">
				 
				<input type="text" name="case_client_extra[phone]" value="<?=$this->value('case_client_extra/phone');?>" placeholder="电话" display-for="new" style="width:20%" />
				<input type="text" name="case_client_extra[email]" value="<?=$this->value('case_client_extra/email');?>" placeholder="电子邮件" display-for="new" style="width:20%" />

				<span display-for="new client">
					<label>来源：</label>
					<select name="case_client_extra[source_type]" style="width:15%">
						<?=options(array('_ENUM','client_source','type'),$this->value('case_client_extra/source_type'))?>
					</select>
					<input type="text" name="case_client_extra[source_detail]" value="<?=$this->value('case_client_extra/source_detail')?>" style="width:10%" />
					<input type="text" name="case_client_extra[source_lawyer_name]" placeholder="来源律师" value="<?=$this->value('case_client_extra/source_lawyer_name')?>" style="width:10%" />
				</span>
				<input type="submit" name="submit[case_client]" value="添加" />
			</div>
		 </div>
	
		<div class="item" name="staff">
			<div class="title"><label>律师：</label>
				<label class="toggle-add-form"><? if($this->value('case_lawyer_extra/show_add_form'))echo '-';else echo '+'?></label>
<?if($responsible_partner==$this->user->id && !$case['lawyer_lock'] && $case['is_reviewed']){?>
				<input type="submit" name="submit[lock_lawyer]" value="锁定" />
<? }?>
<? if($responsible_partner==$this->user->id && $case['lawyer_lock']){ ?>
				<input type="submit" name="submit[unlock_lawyer]" value="解锁" />
<? } ?>
			</div>
	
			<?=$staff_list?>
			
			<div class="add-form hidden">
				<input type="text" name="case_lawyer_extra[lawyer_name]" value="<?=$this->value('case_lawyer_extra/lawyer_name');?>" placeholder="姓名" autocomplete-model="staff" style="width:200px;" />
				<input name="case_lawyer[lawyer]" class="hidden" />
				<select name="case_lawyer[role]">
					<?=options($case_lawyer_role_array,$this->value('case_lawyer/role'));?>
				</select>
				<input type="text" name="case_lawyer_extra[actual_contribute]" value="<?=$this->value('case_lawyer_extra/actual_contribute')?>" placeholder="%" style="display:none;width:22%;" disabled />
				<input type="submit" name="submit[case_lawyer]" value="添加" />
			</div>
		</div>
		
<? if($case['is_query']){//咨询阶段显示报价情况，不显示律师费和办案费?>
		<div class="item">
			<div class="title"><label>报价：</label></div>
			<input type="text" name="cases[quote]" value="<?=$this->value('cases/quote') ?>" />
		</div>
<? }?>
		<div class="item">
			<div class="title">
				<label>签约律师费：</label>
				<label><input type="checkbox" name="cases[timing_fee]" value="1" <? if($this->value('cases/timing_fee'))echo 'checked="checked"';if($case['fee_lock'])echo 'disabled';?>/>计时收费</label> 
				<label id="caseFeeAdd" style="display:none">+</label>
				<label id="caseTimingFeeSave">
	
<? if($this->value('cases/timing_fee') && !isset($case_fee_timing_string)){?>
					<input type="submit" name="submit[case_fee_timing]" value="保存" />
<? }?></label>
<? if(($responsible_partner==$this->user->id || $this->user->isLogged('finance')) && !$case['fee_lock']){?>
				<input type="submit" name="submit[lock_fee]" value="锁定" />
<? }?>
<? if(($responsible_partner==$this->user->id || $this->user->isLogged('finance')) && $case['fee_lock']){ ?>
				<input type="submit" name="submit[unlock_fee]" value="解锁" />
<? } ?>
				
<? if($this->user->isLogged('finance')){?>
				<button type="button" onclick="showWindow('account/add?case=<?=$this->value('cases/id')?>')">到账</button>
<? }?>
<? if($this->user->isLogged('finance')){?>
				<input type="submit" name="submit[case_fee_review]" value="忽略" disabled style="display:none" />
<? }?>
			</div>
	
			<div>
				<div id="caseFeeTimingAddForm" <? if(!$this->value('cases/timing_fee'))echo 'style="display:none"';?>>
<?if(isset($case_fee_timing_string) && $case_fee_timing_string!=''){?>
					<?=$case_fee_timing_string?>
<?}else{?>
					包含：<input type="text" name="case_fee_timing[included_hours]" value="<?=$this->value('case_fee_timing/included_hours');?>" style="width:3%" />小时&nbsp;
					账单起始日：<input type="text" name="case_fee_timing_extra[time_start]" value="<?=$this->value('case_fee_timing_extra/time_start')?>" class="date" style="width:11%" />&nbsp;
					账单日：<input type="text" name="case_fee_timing[bill_day]" value="<?=$this->value('case_fee_timing/bill_day')?>" style="width:3%;" />日&nbsp;
					付款日：<input type="text" name="case_fee_timing[payment_day]" value="<?=$this->value('case_fee_timing/payment_day');?>" style="width:3%;" />日&nbsp;
					付款周期：<input type="text" name="case_fee_timing[payment_cycle]" value="<?=$this->value('case_fee_timing/payment_cycle');?>" style="width:3%;" />个月&nbsp;
					合同周期：<input type="text" name="case_fee_timing[contract_cycle]" value="<?=$this->value('case_fee_timing/contract_cycle');?>" style="width:3%;" />个月&nbsp;
<? }?>
				</div>
			</div>
	
			<?=$fee_list?>	
<? if(!$case['fee_lock']){?>
			<div id="caseFeeAddForm">
				<select style="width:25%;" name="case_fee[type]">
					<?=options($case['is_query']?array('咨询费'):array('固定','风险','计时预付'));?>
				</select>
				<input type="text" name="case_fee[fee]" value="<?=$this->value('case_fee/fee');?>" placeholder="数额" style="width:24%;" />
				<input type="text" name="case_fee[condition]" value="<?=$this->value('case_fee/condition');?>" placeholder="付款条件" style="width:24%" />
				<input type="text" name="case_fee_extra[pay_time]" value="<?=$this->value('case_fee_extra/pay_time')?>" placeholder="预估日期" class="date" style="width:15%" />
				<input type="submit" name="submit[case_fee]" value="添加" />
			</div>
<? }?>
		</div>
	
<?if(!$case['is_query']){?>
		<div class="item">
			<div class="title"><label>办案费约定情况：</label><label id="caseFeeMiscAdd" style="display:none">+</label></div>
	
			<?=$miscfee_list?>
			<div id="caseFeeMiscAddForm">
				<select name="case_fee_misc[receiver]" style="width:25%">
					<?=options(array('承办律师','律所'));?>
				</select>
				<input type="text" name="case_fee_misc[fee]" value="<?=$this->value('case_fee_misc/fee');?>" placeholder="数额" style="width:24%;"  />
				<input type="text" name="case_fee_misc[comment]" value="<?=$this->value('case_fee_misc/comment');?>" placeholder="付款条件" style="width:24%" />
				<input type="text" name="case_fee_misc_extra[pay_time]" value="<?=$this->value('case_fee_misc_extra/pay_time')?>" placeholder="预估日期" class="date" style="width:15%" />
				<input type="submit" name="submit[case_fee_misc]" value="添加" />
			</div>
		</div>
<?}?>
	
		<div class="item">
			<div class="title"><label>文件：</label>
<? if($this->value('cases/apply_file')){ ?>
				<input type="submit" name="submit[file_document_list]" value="下载目录" />
<? } ?>
			</div>
	
			<?=$document_list?>

			<div id="caseDocumentAddForm">
				<input type="file" name="file" id="file" width="30%" />
				<select name="case_document[doctype]" style="width:15%">
				<?=options(array('接洽资料','身份资料','聘请委托文书','签约合同（扫描）','办案文书','裁判文书','行政文书','证据材料','其他'),$this->value('case_document/doctype'));?>
				</select>
				<input type="text" name="case_document[comment]" placeholder="具体文件名称" style="width:35%" />
				<input type="submit" name="submit[case_document]" value="上传" />
			</div>
		</div>
	
		<div class="item">
			<div class="title">
				<span class="right">
					<?=$schedule_time?>小时
					<a href="/schedule/lists?case=<?=$this->value('cases/id')?>">所有日志>></a>
				</span>
				<label>最新日志：
					<a href="javascript:showWindow('schedule/add?case=<?=$this->value('cases/id')?>')">添加>></a>
				</label>
			</div>
			<?=$schedule_list?>
		</div>
	
		<div class="item">
			<div class="title">
				<span class="right">
					<a href="/schedule/plan?case=<? echo $this->value('cases/id')?>">所有计划>></a>
				</span>
				<label>日程计划：
					<a href="javascript:showWindow('schedule/add?case=<?=$this->value('cases/id')?>&completed=0')">添加>></a>
				</label>
			</div>
			<?$plan_list?>
		</div>
	
<? if(!$case['is_query'] && $case['classification']!='法律顾问'){?>
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
			<input type="submit" name="submit[cases]" value="保存" />
			<input type="submit" name="submit[cancel]" value="取消" />
		</div>
	</div>
</div>
</form>
<?=javascript('case_add')?>