<form method="post" name="<?=CONTROLLER?>" id="<?=$project['id']?>">
	<div class="item">
		<div class="title">
			<label class="num"><?=$this->value('project/num');?></label>
		</div>

		<select id="type" name="labels[领域]"<?php if(in_array('类型已锁定',$labels)){ ?> disabled="disabled"<?php } ?>>
			<?=options($this->config->user_item('案件领域'),$this->value('labels/领域'),'领域');?>
		</select>
		
		<select id="classification" name="labels[分类]"<?php if(in_array('类型已锁定',$labels)){ ?> disabled="disabled"<?php } ?>>
			<?=options(array('争议','非争议','法律顾问'),$this->value('labels/分类'),'分类');?>
		</select>
		
		<select name="labels[阶段]" 
			class="<?php if(!isset($labels['分类']) || $labels['分类']!='争议'){ ?>hidden<?php } ?>"
			<?php if(!isset($labels['分类']) || $labels['分类']!='争议'){ ?> disabled="disabled"<?php } ?>
		>
			<?=options($this->cases->getRelatedLabels($labels, '阶段'),$this->value('labels/阶段'),'阶段');?>
		</select>
		
		<input type="text" name="project[name]" value="<?=$this->value('project/name')?>" placeholder="<?=lang(CONTROLLER)?>名称" style="width:300px;">
		
<?php if(!$project['num'] && $project['type']==='cases'){ ?>
		<button type="submit" name="submit[apply_num]" class="major">获得案号</button>
<?php } ?>

<?php if($project['type']==='query'){ ?>
		<input type="text" name="project[first_contact]" value="<?=$this->value('project/first_contact')?>" placeholder="首次接待日期" title="首次接待日期" class="date" />
<?php }else{ ?>
		<input type="text" name="project[time_contract]" value="<?=$this->value('project/time_contract')?>" placeholder="立案日期" title="立案日期" class="date" <? if(in_array('在办',$labels))echo 'disabled';?> />
		-
		<input type="text" name="project[end]" value="<?=$this->value('project/end')?>" placeholder="预估结案日期" title="预估结案日期" class="date" <? if(in_array('在办',$labels))echo 'disabled';?> />
<?php } ?>
		<span><?=$this->value('profiles/案源类型')?></span>
		<span>案源系数：<?=round($this->value('profiles/案源系数')*100,2)?>%</span>
	</div>

	<div class="item" name="client">
		<div class="title"><label>客户及相关人：</label>
<? if(isset($people_roles[$this->user->id]) && $this->user->isLogged('manager') && !in_array('客户已锁定',$labels)){ ?>
			<button type="submit" name="submit[lock_client]">锁定</button>
<? }?>
<? if(isset($people_roles[$this->user->id]) && $this->user->isLogged('manager') && in_array('客户已锁定',$labels)){ ?>
			<button type="submit" name="submit[unlock_client]">解锁</button>
<? } ?>
		</div>

		<?=$client_list?>

		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="hidden" name="case_client[client]" class="tagging" data-placeholder="名称" data-ajax="/people/match/" />

			<select name="case_client[role]" class="chosen allow-new" data-placeholder="本案地位">
				<?=options($this->cases->getRelatedRoles($labels),$this->value('case_client/role'),'',false,false,false);?>
			</select>

			<button type="submit" name="submit[client]">添加</button>
		</span>
	 </div>

<? if(in_array('争议',$labels)){ ?>
	<div class="item">
		<div class="title"><label>争议焦点：</label></div>
		<input name="project[focus]" type="text" value="<?=$this->value('project/focus')?>" style="width:99%;font-size:1.2em;" />
	</div>
<? }?>

<? if(in_array('非争议',$labels)){ ?>
	<div class="item">
		<div class="title"><label>案件标的：</label></div>
		<input name="project[focus]" type="text" value="<?=$this->value('project/focus')?>" style="width:99%;font-size:1.2em;" />
	</div>
<? }?>
	
	<div class="item" name="staff"<?php if(in_array('职员已锁定',$labels)){ ?> locked="locked"<?php } ?>>
		<div class="title"><label>律师：</label>
<?php if(isset($people_roles[$this->user->id]) && $this->user->isLogged('manager') && !in_array('职员已锁定',$labels)){ ?>
			<button type="submit" name="submit[lock_staff]">锁定</button>
<? }?>
<? if(isset($people_roles[$this->user->id]) && $this->user->isLogged('manager') && in_array('职员已锁定',$labels)){ ?>
			<button type="submit" name="submit[unlock_staff]">解锁</button>
<? } ?>
		</div>

		<?=$staff_list?>

		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="hidden" name="staff[id]" class="tagging" data-placeholder="姓名" data-ajax='/staff/match/'>
			<select name="staff[role]" class="chosen allow-new" data-placeholder="本案职务">
				<?=options($this->cases->getRelatedRoles($labels),$this->value('staff/role'),'',false,false,false);?>
			</select>
			<input type="text" name="staff[weight]" value="<?=$this->value('staff/weight')?>" placeholder="占比%" />
			<button type="submit" name="submit[staff]">添加</button>
		</span>
	</div>

	<div class="item" name="account">
		<div class="title">
			<label>资金（外部 计入创收）：</label>
			
<? if((isset($people_roles[$this->user->id]) && $this->user->isLogged('manager') || $this->user->isLogged('finance')) && !in_array('费用已锁定',$labels)){ ?>
			<button type="submit" name="submit[lock_fee]">锁定</button>
<? }?>
<? if((isset($people_roles[$this->user->id]) && $this->user->isLogged('manager') || $this->user->isLogged('finance')) && in_array('费用已锁定',$labels)){ ?>
			<button type="submit" name="submit[unlock_fee]">解锁</button>
<? } ?>
		</div>

		<?=$account_list?>	
		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<select name="account[type]" class="tagging allow-new">
				<?=options(array('固定','风险','计时预付','律师服务费'),$this->value('account/type'),'类型');?>
			</select>
			<input type="text" name="account[account]" value="<?=$this->value('account/account')?>" placeholder="帐目编号" />
			<input type="text" name="account[amount]" value="<?=$this->value('account/amount')?>" placeholder="数额" />
			<input type="hidden" name="account[count]" value="1" />
<?php if($this->user->isLogged('finance')){ ?>
			<label><input type="checkbox" value="1" name="account[received]">已到帐</label>
<?php } ?>
			<input type="text" name="account[date]" value="<?=$this->value('account/date')?>" placeholder="预估日期" class="date" />
			<input type="hidden" name="account[people]" data-ajax="/people/match/" data-placeholder="收款/付款人" class="tagging">
			<input type="text" name="account[comment]" value="<?=$this->value('account/comment');?>" placeholder="条件/备注" />
			<button type="submit" name="submit[account]">添加</button>
		</span>
	</div>

	<div class="item" name="fee">
		<div class="title">
			<label>资金（内部 不计入创收）：</label>
			
<? if((isset($people_roles[$this->user->id]) && $this->user->isLogged('manager') || $this->user->isLogged('finance')) && !in_array('费用已锁定',$labels)){ ?>
			<button type="submit" name="submit[lock_fee]">锁定</button>
<? }?>
<? if((isset($people_roles[$this->user->id]) && $this->user->isLogged('manager') || $this->user->isLogged('finance')) && in_array('费用已锁定',$labels)){ ?>
			<button type="submit" name="submit[unlock_fee]">解锁</button>
<? } ?>
		</div>

		<?=$fee_list?>	
		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<select name="fee[type]" class="tagging allow-new">
				<?=options(array('办案费','公关费用'),$this->value('fee/type'),'类型');?>
			</select>
			<input type="text" name="fee[account]" value="<?=$this->value('fee/fee')?>" placeholder="帐目编号" />
			<input type="text" name="fee[amount]" value="<?=$this->value('fee/amount')?>" placeholder="数额" />
			<input type="hidden" name="fee[count]" value="0" />
<?php if($this->user->isLogged('finance')){ ?>
			<label><input type="checkbox" value="1" name="fee[received]">已到帐</label>
<?php } ?>
			<input type="text" name="fee[date]" value="<?=$this->value('fee/date')?>" placeholder="预估日期" class="date" />
			<input type="hidden" name="fee[people]" data-ajax="/people/match/" data-placeholder="收款/付款人" class="tagging">
			<input type="text" name="fee[comment]" value="<?=$this->value('fee/comment');?>" placeholder="条件/备注" />
			<button type="submit" name="submit[fee]">添加</button>
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
				<a href="#schedule/plan?project=<?=$this->value('project/id')?>">所有计划>></a>
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