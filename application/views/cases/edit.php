<? javascript('case_add')?>
<? javascript('Jeditable/jquery.jeditable.mini')?>
<form method="post" name="formCaseAdd" enctype="multipart/form-data">
<div class="contentTableMenu">
	<div class="right">
		<input type="submit" name="submit[cases]" value="保存" />

		<? if($responsible_partner==$_SESSION['id'] && !post('cases/is_reviewed') && !post('cases/is_query')){?>
		<button type="button" name="submit[review]">立案审核</button>
		<? }?>

		<? if($responsible_partner!=$_SESSION['id'] && !post('cases/client_lock') && post('cases/is_reviewed')){?>
		<input type="submit" name="submit[apply_lock]" value="申请锁定" />
		<? }?>

		<? if(is_logged('finance') && post('cases/apply_file') && !post('cases/finance_review')){?>
		<input type="submit" name="submit[review_finance]" value="财务审核" />
		<? }?>

		<? if(is_logged('admin') && post('cases/apply_file') && !post('cases/info_review')){?>
		<input type="submit" name="submit[review_info]" value="信息审核" />
		<? }?>

		<? if(is_logged('manager') && post('cases/apply_file') && !post('cases/manager_review')){?>
		<input type="submit" name="submit[review_manager]" value="主管审核" />
		<? }?>
		
		<? if(is_logged('admin') && post('cases/apply_file') && post('cases/finance_review') && post('cases/info_review') && post('cases/manager_review') && !post('cases/filed')){?>
		<input type="submit" name="submit[file]" value="实体归档" />
		<? }?>
		
		<? if(post('cases/is_query')){ ?>
		<input type="submit" name="submit[new_case]" value="立案" />
		<input type="submit" name="submit[file]" value="归档" />
		<? } ?>

		<? if(!post('cases/apply_file') &&
			post('cases/is_reviewed') && 
			post('cases/type_lock') && 
			post('cases/client_lock') &&
			post('cases/lawyer_lock') &&
			post('cases/fee_lock')
		){?>
		<input type="submit" name="submit[apply_file]" value="申请归档" />
		<? }?>

		<input type="submit" name="submit[cancel]" value="取消" />
	</div>
</div>

<div class="contentTableBox">
	<div class="contentTable">
		<div class="item">
			<div class="title"><label>客户及相关人：</label>
				<label id="caseClientAdd"><? if(post('case_client_extra/show_add_form'))echo '-';else echo '+'?></label>
				<? if($responsible_partner==$_SESSION['id'] && !post('cases/client_lock') && post('cases/is_reviewed')){?>
				<input type="submit" name="submit[lock_client]" value="锁定" />
				<? }?>
				<? if($responsible_partner==$_SESSION['id'] && post('cases/client_lock')){ ?>
				<input type="submit" name="submit[unlock_client]" value="解锁" />
				<? } ?>
			</div>
		
			<?=$case_client_table?>
	
			<div id="caseClientAddForm" <? if(!post('case_client_extra/show_add_form'))echo 'style="display:none"';?>>
				<input type="text" name="case_client_extra[name]" value="<?=post('case_client_extra/name')?>" placeholder="名称" autocomplete="client" autocomplete-input-name="case_client[client]" style="width:20%" />
	
				<span class="autocomplete-no-result-menu">
					<? displayCheckbox('单位','case_client_extra[character]',post('case_client_extra/character'),'单位')?>
				
					<select name="case_client_extra[classification]" disabled="disabled" style="width:15%">
						<? displayOption(post('cases/client_lock')?array('联系人','相对方'):array('客户','相对方','联系人'),post('case_client_extra/classification'));?>
					</select>
		
					<select name="case_client_extra[type]" disabled="disabled" style="width:15%">
						<? displayOption(post('cases/client_lock')?'联系人':'客户',post('case_client_extra/type'),false,'type','classification','type',"affair='client'");?>
					</select>
				</span>
				
				<span id="caseClientAddFormForContact" <?if(post('case_client_extra/classification')=='客户')echo 'style="display:none"'?>>
					<input type="text" name="case_client_extra[work_for]" placeholder="工作单位" <?if(post('case_client_extra/classification')=='客户')echo 'disabled="disabled"'?> style="width:10%" />
				</span>
	
				<? if(!post('cases/is_query')){ ?>
				<label>本案地位：</label>
				<select name="case_client[role]" style="width:15%">
					<? displayOption(array('原告','被告','第三人','上诉人','被上诉人','申请人','被申请人','对方代理人','法官','检察官','其他'),post('case_client/role'));?>
				</select>
				<? } ?>
	
				<br class="autocomplete-no-result-menu" />
				
				<span class="autocomplete-no-result-menu">
					<input type="text" name="case_client_extra[phone]" value="<?=post('case_client_extra/phone');?>" placeholder="电话" disabled="disabled" style="width:20%" />
					<input type="text" name="case_client_extra[email]" value="<?=post('case_client_extra/email');?>" placeholder="电子邮件" disabled="disabled" style="width:20%" />
	
					<span id="caseClientAddFormForClient" class="autocomplete-no-result-menu">
						<label>来源：</label>
						<select name="case_client_extra[source_type]" disabled="disabled" style="width:15%">
							<? displayOption(array('_ENUM','client_source','type'),post('case_client_extra/source_type'))?>
						</select>
						<input type="text" name="case_client_extra[source_detail]" value="<?=post('case_client_extra/source_detail')?>" style="width:10%" <? if(!in_array(post('case_client_extra/source_type'),array('其他网络','媒体','老客户介绍','合作单位介绍','其他')))echo 'disabled="disabled"';?> />
						<input type="text" name="case_client_extra[source_lawyer_name]" placeholder="来源律师" disabled="disabled" value="<?=post('case_client_extra/source_lawyer_name')?>" style="width:10%" />
					</span>
				</span>
				<input type="submit" name="submit[case_client]" value="添加" />
			</div>
		 </div>
	
		<div class="item">
			<?if(post('cases/num')){?>
			<div class="title"><label title="内部ID：<?=post('cases/id')?>"><?=post('cases/num');?></label></div>
	
			<div class="field" id="case_name">
				<span class="right">
					<? echo $case_status?>
				</span>
	
				<?=post('cases/name')?>
				&nbsp;
			</div>
			<?}?>
			
			<? if(post('cases/classification')=='内部行政'){?>
			<span class="field">内部行政</span>
			<? }else{?>
			<select id="type" style="width:7%;" name="cases[type]" <? if(post('cases/type_lock'))echo 'disabled="disabled"';?>>
			<? displayOption(array('公司','房产建筑','刑事行政','婚姻家庭','诉讼','知识产权','劳动人事','涉外','日韩'),post('cases/type'));?>
			</select>
				<? if(post('cases/is_query')){ ?>
			<select id="classification" style="width:15%;" name="cases[query_type]" <? if(post('cases/type_lock'))echo 'disabled="disabled"';?>>
			<? displayOption(array('_ENUM','case','query_type'),post('cases/query_type'));?>
			</select>
				<? }else{ ?>
			<select id="classification" style="width:15%;" name="cases[classification]" <? if(post('cases/type_lock'))echo 'disabled="disabled"';?>>
			<? displayOption(array('诉讼','非诉讼','法律顾问','内部行政'),post('cases/classification'));?>
			</select>
			<select id="stage" style="width:15%;" name="cases[stage]" <? if(post('cases/type_lock'))echo 'disabled="disabled"';?>>
			<? displayOption($case_type_array,post('cases/stage'));?>
			</select>
				<? } ?>
			<? }?>
	
			<? if(post('cases/is_query')){ ?>
			<input type="text" name="cases[first_contact]" value="<?=post('cases/first_contact')?>" placeholder="首次接待日期" title="首次接待日期" class="date" style="width:100px" />
			<? }else{ ?>
			<input type="text" name="cases[time_contract]" value="<?=post('cases/time_contract')?>" placeholder="立案日期" title="立案日期" class="date" style="width:100px" <? if(post('cases/is_reviewed'))echo 'disabled="disabled"';?> />
			-
			<input type="text" name="cases[time_end]" value="<?=post('cases/time_end')?>" placeholder="预估结案日期" title="预估结案日期" class="date" style="width:100px" <? if(post('cases/is_reviewed'))echo 'disabled="disabled"';?> />
			<? } ?>
	
			<? if(!post('cases/num')){?>
			<input type="submit" name="submit[apply_case_num]" value="获得案号" />
			<? }else{?>
			<input type="text" name="cases[name_extra]" style="width:20%" value="<?=post('cases/name_extra')?>" placeholder="后缀" />
			<? }?>
		</div>
	
		<div class="item">
			<div class="title"><label>律师：</label>
				<label id="caseLawyerAdd"><? if(post('case_lawyer_extra/show_add_form'))echo '-';else echo '+'?></label>
				<? if($responsible_partner==$_SESSION['id'] && !post('cases/lawyer_lock') && post('cases/is_reviewed')){?>
				<input type="submit" name="submit[lock_lawyer]" value="锁定" />
				<? }?>
				<? if($responsible_partner==$_SESSION['id'] && post('cases/lawyer_lock')){ ?>
				<input type="submit" name="submit[unlock_lawyer]" value="解锁" />
				<? } ?>
			</div>
	
			<?=$case_staff_table?>
			
			<div id="caseLawyerAddForm" <? if(!post('case_lawyer_extra/show_add_form'))echo 'style="display:none"';?>>
				<input type="text" name="case_lawyer_extra[lawyer_name]" value="<?=post('case_lawyer_extra/lawyer_name');?>" placeholder="姓名" style="width:45%" />
				<select style="width:45%" name="case_lawyer[role]">
					<? displayOption($case_lawyer_role_array,post('case_lawyer/role'));?>
				</select>
				<input type="text" name="case_lawyer_extra[actual_contribute]" value="<?=post('case_lawyer_extra/actual_contribute')?>" placeholder="%" style="display:none;width:22%;" disabled="disabled" />
				<input type="submit" name="submit[case_lawyer]" value="添加" />
			</div>
		</div>
		
		<? if(post('cases/is_query')){//咨询阶段显示报价情况，不显示律师费和办案费?>
		<div class="item">
			<div class="title"><label>报价：</label></div>
			<input type="text" name="cases[quote]" value="<?=post('cases/quote') ?>" />
		</div>
		<? }?>
		<div class="item">
			<div class="title">
				<label>签约律师费：</label>
				<label><input type="checkbox" name="cases[timing_fee]" value="1" <? if(post('cases/timing_fee'))echo 'checked="checked"';if(post('cases/fee_lock'))echo 'disabled="disabled"';?>/>计时收费</label> 
				<label id="caseFeeAdd" style="display:none">+</label>
				<label id="caseTimingFeeSave">
	
				<? if(post('cases/timing_fee') && !isset($case_fee_timing_string)){?>
					<input type="submit" name="submit[case_fee_timing]" value="保存" />
				<? }?></label>
	
				<? if(($responsible_partner==$_SESSION['id'] || is_logged('finance')) && !post('cases/fee_lock')){?>
				<input type="submit" name="submit[lock_fee]" value="锁定" />
				<? }?>
				<? if(($responsible_partner==$_SESSION['id'] || is_logged('finance')) && post('cases/fee_lock')){ ?>
				<input type="submit" name="submit[unlock_fee]" value="解锁" />
				<? } ?>
				
				<? if(is_logged('finance')){?>
				<button type="button" onclick="showWindow('account/add?case=<?=post('cases/id')?>')">到账</button>
				<? }?>
				
				<? if(is_logged('finance')){?>
				<input type="submit" name="submit[case_fee_review]" value="忽略" disabled="disabled" style="display:none" />
				<? }?>
			</div>
	
			<div class="title">
				<div id="caseFeeTimingAddForm" <? if(!post('cases/timing_fee'))echo 'style="display:none"';?>>
					<?if(isset($case_fee_timing_string) && $case_fee_timing_string!=''){?>
					<?=$case_fee_timing_string?>
					<?}else{?>
					包含：<input type="text" name="case_fee_timing[included_hours]" value="<?=post('case_fee_timing/included_hours');?>" style="width:3%" />小时&nbsp;
					账单起始日：<input type="text" name="case_fee_timing_extra[time_start]" value="<?=post('case_fee_timing_extra/time_start')?>" class="date" style="width:11%" />&nbsp;
					账单日：<input type="text" name="case_fee_timing[bill_day]" value="<?=post('case_fee_timing/bill_day')?>" style="width:3%;" />日&nbsp;
					付款日：<input type="text" name="case_fee_timing[payment_day]" value="<?=post('case_fee_timing/payment_day');?>" style="width:3%;" />日&nbsp;
					付款周期：<input type="text" name="case_fee_timing[payment_cycle]" value="<?=post('case_fee_timing/payment_cycle');?>" style="width:3%;" />个月&nbsp;
					合同周期：<input type="text" name="case_fee_timing[contract_cycle]" value="<?=post('case_fee_timing/contract_cycle');?>" style="width:3%;" />个月&nbsp;
					<? }?>
				</div>
			</div>
	
			<?=$case_fee_table?>	
			<? if(!post('cases/fee_lock')){?>
			<div id="caseFeeAddForm">
				<select style="width:25%;" name="case_fee[type]">
					<? displayOption(post('cases/is_query')?array('咨询费'):array('固定','风险','计时预付'));?>
				</select>
				<input type="text" name="case_fee[fee]" value="<?=post('case_fee/fee');?>" placeholder="数额" style="width:24%;" />
				<input type="text" name="case_fee[condition]" value="<?=post('case_fee/condition');?>" placeholder="付款条件" style="width:24%" />
				<input type="text" name="case_fee_extra[pay_time]" value="<?=post('case_fee_extra/pay_time')?>" placeholder="预估日期" class="date" style="width:15%" />
				<input type="submit" name="submit[case_fee]" value="添加" />
			</div>
			<? }?>
		</div>
	
		<?if(!post('cases/is_query')){?>
		<div class="item">
			<div class="title"><label>办案费约定情况：</label><label id="caseFeeMiscAdd" style="display:none">+</label></div>
	
			<?=$case_fee_misc_table?>
			<div id="caseFeeMiscAddForm">
				<select name="case_fee_misc[receiver]" style="width:25%">
					<? displayOption(array('承办律师','律所'));?>
				</select>
				<input type="text" name="case_fee_misc[fee]" value="<?=post('case_fee_misc/fee');?>" placeholder="数额" style="width:24%;"  />
				<input type="text" name="case_fee_misc[comment]" value="<?=post('case_fee_misc/comment');?>" placeholder="付款条件" style="width:24%" />
				<input type="text" name="case_fee_misc_extra[pay_time]" value="<?=post('case_fee_misc_extra/pay_time')?>" placeholder="预估日期" class="date" style="width:15%" />
				<input type="submit" name="submit[case_fee_misc]" value="添加" />
			</div>
		</div>
		<?}?>
	
		<div class="item">
			<div class="title"><label>文件：</label>
				<? if(post('cases/apply_file')){ ?>
				<input type="submit" name="submit[file_document_list]" value="下载目录" />
				<? } ?>
			</div>
	
			<?=$case_document_table?>

			<div id="caseDocumentAddForm">
				<input type="file" name="file" id="file" width="30%" />
				<select name="case_document[doctype]" style="width:15%">
				<? displayOption(array('接洽资料','身份资料','聘请委托文书','签约合同（扫描）','办案文书','裁判文书','行政文书','证据材料','其他'),post('case_document/doctype'));?>
				</select>
				<input type="text" name="case_document[comment]" placeholder="具体文件名称" style="width:35%" />
				<input type="submit" name="submit[case_document]" value="上传" />
			</div>
		</div>
	
		<div class="item">
			<div class="title">
				<span class="right">
					<? echo $schedule_time; ?>小时
					<a href="/schedule/lists?case=<? echo post('cases/id')?>">所有日志>></a>
				</span>
				<label>最新日志：
					<a href="javascript:showWindow('schedule/add?case=<? echo post('cases/id')?>')">添加>></a>
				</label>
			</div>
			<?=$case_schedule_table?>
		</div>
	
		<div class="item">
			<div class="title">
				<span class="right">
					<a href="/schedule/plan?case=<? echo post('cases/id')?>">所有计划>></a>
				</span>
				<label>日程计划：
					<a href="javascript:showWindow('schedule/add?case=<? echo post('cases/id')?>&completed=0')">添加>></a>
				</label>
			</div>
			<?$case_plan_table?>
		</div>
	
		<? if(!post('cases/is_query') && post('cases/classification')!='法律顾问'){?>
		<div class="item">
			<div class="title"><label>争议焦点：（案件标的）</label></div>
			<textarea class="item" name="cases[focus]" type="text" rows="2"><?=post('cases/focus')?></textarea>
		</div>
		<? }?>
	
		<div class="item">
			<div class="title"><label>案情简介：</label></div>
			<textarea class="item" name="cases[summary]" type="text" rows="4"><?=post('cases/summary')?></textarea>
		</div>
	
		<div class="item">
			<div class="title"><label>备注：</label></div>
			<textarea class="item" name="cases[comment]" type="text" rows="3"><?=post('cases/comment')?></textarea>
		</div>
	
		<div class="submit">
			<input type="submit" name="submit[cases]" value="保存" />
			<input type="submit" name="submit[cancel]" value="取消" />
		</div>
	</div>
</div>
</form>