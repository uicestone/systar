<form method="post" name="<?=CONTROLLER?>" id="<?=$this->project->id?>" enctype="multipart/form-data" encoding="multipart/form-data">
	<div class="item">
		<div class="title">
			<label class="num" title="内部ID：<?=$this->value('project/id')?>"><?=$this->value('project/num');?></label>
		</div>

		<select id="type" name="labels[领域]"<?if(in_array('类型已锁定',$labels)){?> disabled="disabled"<?}?>>
		<?=options($this->config->item('案件领域'),$this->value('labels/领域'),'领域');?>
		</select>
		<select id="classification" name="labels[分类]"<?if(in_array('类型已锁定',$labels)){?> disabled="disabled"<?}?>>
		<?=options(array('争议','非争议','法律顾问'),$this->value('labels/分类'),'分类');?>
		</select>
		<select name="labels[阶段]"<?if(!isset($labels['分类']) || $labels['分类']!='争议'){?> class="hidden" disabled="disabled"<?}?>>
		<?=options($case_type_array,$this->value('labels/阶段'),'阶段');?>
		</select>
		<input type="text" name="project[name]" value="<?=$this->value('project/name')?>" placeholder="案件名称" style="width:300px;">
<?	if(!$project['num']){?>
		<button type="submit" name="submit[apply_num]" class="major">获得案号</button>
<?	}?>
		<input type="text" name="project[time_contract]" value="<?=$this->value('project/time_contract')?>" placeholder="立案日期" title="立案日期" class="date" <? if(in_array('在办',$labels))echo 'disabled';?> />
		-
		<input type="text" name="project[end]" value="<?=$this->value('project/end')?>" placeholder="预估结案日期" title="预估结案日期" class="date" <? if(in_array('在办',$labels))echo 'disabled';?> />
	</div>

	<div class="item" name="client">
		<div class="title"><label>客户及相关人：</label>
<? if(isset($people_roles[$this->user->id]) && in_subarray('督办人', $people_roles[$this->user->id], 'role') && !in_array('客户已锁定',$labels)){?>
			<button type="submit" name="submit[lock_client]">锁定</button>
<? }?>
<? if(isset($people_roles[$this->user->id]) && in_subarray('督办人', $people_roles[$this->user->id], 'role') && in_array('客户已锁定',$labels)){ ?>
			<button type="submit" name="submit[unlock_client]">解锁</button>
<? } ?>
		</div>

		<?=$client_list?>

		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="text" name="client[name]" value="<?=$this->value('client/name')?>" placeholder="名称" autocomplete-model="client" />
			<input type="text" name="case_client[client]" class="hidden" />

			<select name="case_client[role]" class="chosen allow-new" data-placeholder="本案地位">
				<?=options(array('原告','被告','第三人','上诉人','被上诉人','申请人','被申请人','对方代理人','法官','检察官'),$this->value('case_client/role'),'');?>
			</select>

			<span display-for="new" class="hidden">
				<?=checkbox('单位','client[character]',$this->value('client/character'),'单位','disabled="disabled"')?>

				<select name="client[type]" disabled="disabled">
					<?=options(in_array('客户已锁定',$labels)?array('client'=>lang('client')):array('client'=>lang('client'),'contact'=>lang('contact')),$this->value('client/type'),'人员类型',true);?>
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
			<button type="submit" name="submit[client]">添加</button>
		</span>
	 </div>

<? if(in_array('争议',$labels)){?>
	<div class="item">
		<div class="title"><label>争议焦点：</label></div>
		<input name="project[focus]" type="text" value="<?=$this->value('project/focus')?>" style="width:99%;font-size:1.2em;" />
	</div>
<? }?>

<? if(in_array('非争议',$labels)){?>
	<div class="item">
		<div class="title"><label>案件标的：</label></div>
		<input name="project[focus]" type="text" value="<?=$this->value('project/focus')?>" style="width:99%;font-size:1.2em;" />
	</div>
<? }?>
	
	<div class="item" name="staff"<?if(in_array('职员已锁定',$labels)){?> locked="locked"<?}?>>
		<div class="title"><label>律师：</label>
<?if(isset($people_roles[$this->user->id]) && in_subarray('督办人', $people_roles[$this->user->id], 'role') && !in_array('职员已锁定',$labels)){?>
			<button type="submit" name="submit[lock_staff]">锁定</button>
<? }?>
<? if(isset($people_roles[$this->user->id]) && in_subarray('督办人', $people_roles[$this->user->id], 'role') && in_array('职员已锁定',$labels)){ ?>
			<button type="submit" name="submit[unlock_staff]">解锁</button>
<? } ?>
		</div>

		<?=$staff_list?>

		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="text" name="staff[name]" value="<?=$this->value('staff/name');?>" placeholder="姓名" autocomplete-model="staff" />
			<input name="staff[id]" class="hidden" />
			<select name="staff[role]" class="chosen allow-new" data-placeholder="本案职务">
				<?=options($staff_role_array,$this->value('staff/role'),'');?>
			</select>
			<input type="text" name="staff[weight]" value="<?=$this->value('staff/weight')?>" placeholder="占比%" />
			<button type="submit" name="submit[staff]">添加</button>
		</span>
	</div>

	<div class="item" name="account">
		<div class="title">
			<label>签约律师费：</label>
			
<? if((isset($people_roles[$this->user->id]) && in_subarray('督办人', $people_roles[$this->user->id], 'role') || $this->user->isLogged('finance')) && !in_array('费用已锁定',$labels)){?>
			<button type="submit" name="submit[lock_fee]">锁定</button>
<? }?>
<? if((isset($people_roles[$this->user->id]) && in_subarray('督办人', $people_roles[$this->user->id], 'role') || $this->user->isLogged('finance')) && in_array('费用已锁定',$labels)){ ?>
			<button type="submit" name="submit[unlock_fee]">解锁</button>
<? } ?>
		</div>

		<?=$fee_list?>	
		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<select name="account[type]">
				<?=options(array('固定','风险','计时预付'),$this->value('account/type'),'类型');?>
			</select>
			<input type="text" name="account[account]" value="<?=$this->value('account/account')?>" placeholder="帐目编号" />
			<input type="text" name="account[amount]" value="<?=$this->value('account/amount');?>" placeholder="数额" />
<?if($this->user->isLogged('finance')){?>
			<select name="account[received]">
				<option value="0">应收帐款</option>
				<option value="1">已到帐</option>
			</select>
<?}?>
			<input type="text" name="account[date]" value="<?=$this->value('account/date')?>" placeholder="预估日期" class="date" />
			<input type="text" name="account[comment]" value="<?=$this->value('account/comment');?>" placeholder="条件/备注" />
			<button type="submit" name="submit[account]">添加</button>
		</span>
	</div>

	<div class="item" name="miscfee">
		<div class="title"><label>办案费约定情况：</label></div>

		<?=$miscfee_list?>
		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<select name="miscfee[receiver]">
				<?=options(array('承办律师','律所'),$this->value('miscfee[receiver]'),'收款方');?>
			</select>
			<input type="text" name="miscfee[account]" value="<?=$this->value('miscfee/account')?>" placeholder="帐目编号" />
			<input type="text" name="miscfee[amount]" value="<?=$this->value('miscfee/amount');?>" placeholder="数额" />
<?if($this->user->isLogged('finance')){?>
			<select name="miscfee[received]">
				<option value="0">应收帐款</option>
				<option value="1">已到帐</option>
			</select>
<?}?>
			<input type="text" name="miscfee[date]" value="<?=$this->value('miscfee/date')?>" placeholder="预估日期" class="date" />
			<input type="text" name="miscfee[comment]" value="<?=$this->value('miscfee/comment');?>" placeholder="条件/备注" />
			<button type="submit" name="submit[miscfee]">添加</button>
		</span>
	</div>

	<div class="item" name="document">
		<div class="title"><label>文件：</label>
<? if($this->value('project/apply_file')){ ?>
			<button type="submit" name="submit[file_document_list]">下载目录</button>
<? } ?>
		</div>

		<?=$document_list?>

		<div class="add-form">
			<input type="file" name="document" id="file" data-url="/document/submit/upload" />
			<input name="document[id]" class="hidden" />
			<input type="text" name="document[name]" placeholder="文件名称" style="padding:4px" />
			<select name="document_labels[]" class="chosen allow-new" data-placeholder="标签" multiple="multiple" style="width:200px;height:15px;">
				<?=options($this->document->getAllLabels(),$this->value('document_labels'));?>
			</select>
			<button type="submit" name="submit[document]">保存</button>
		</div>
	</div>

	<div class="item" name="schedule">
		<div class="title">
			<span class="right">
				<?=(double)$schedule_time?>小时
				<a href="#schedule/lists?project=<?=$this->value('project/id')?>">所有日志>></a>
			</span>
			<label>最新日志：
				<a href="javascript:$.createSchedule({project:<?=$this->value('project/id')?>,completed:true,refreshOnSave:true})">添加>></a>
			</label>
		</div>
		<?=$schedule_list?>
	</div>

	<div class="item" name="plan">
		<div class="title">
			<span class="right">
				<a href="#schedule/plan?project=<? echo $this->value('project/id')?>">所有计划>></a>
			</span>
			<label>日程计划：
				<a href="javascript:$.createSchedule({project:<?=$this->value('project/id')?>,completed:false,refreshOnSave:true})">添加>></a>
			</label>
		</div>
		<?=$plan_list?>
	</div>

	<div class="item">
		<div class="title"><label>案情简介：</label></div>
		<textarea class="item" name="project[summary]" type="text" rows="4"><?=$this->value('project/summary')?></textarea>
	</div>

	<div class="item" name="relative">
		<div class="title"><label>相关案件</label></div>
		<?=$relative_list?>
	</div>

	<div class="item">
		<div class="title"><label>备注：</label></div>
		<textarea class="item" name="project[comment]" type="text" rows="3"><?=$this->value('project/comment')?></textarea>
	</div>
</form>
<?=$this->javascript('project_add')?>
