<form method="post" name="<?=CONTROLLER?>" id="<?=$project['id']?>">
	<div class="item">
		<div class="title">
			<label class="num"><?=$this->value('project/num');?></label>
		</div>

		<select id="type" name="tags[领域]"<?if(in_array('类型已锁定',$tags)){?> disabled="disabled"<?}?>>
			<?=options($this->config->user_item('案件领域'),$this->value('tags/领域'),'领域');?>
		</select>
		
		<select id="classification" name="tags[分类]"<?if(in_array('类型已锁定',$tags)){?> disabled="disabled"<?}?>>
			<?=options(array('争议','非争议','法律顾问'),$this->value('tags/分类'),'分类');?>
		</select>
		
		<select name="tags[阶段]" 
			class="<?if(!isset($tags['分类']) || $tags['分类']!='争议'){?>hidden<?}?>"
			<?if(!isset($tags['分类']) || $tags['分类']!='争议'){?> disabled="disabled"<?}?>
		>
			<?=options($this->cases->getRelatedTags($tags, '阶段'),$this->value('tags/阶段'),'阶段');?>
		</select>
		
		<input type="text" name="project[name]" value="<?=$this->value('project/name')?>" placeholder="<?=lang(CONTROLLER)?>名称" style="width:300px;">
		
<?if(!$project['num'] && $project['type']==='cases'){?>
		<button type="submit" name="submit[apply_num]" class="major">获得案号</button>
<?}?>

<?if($project['type']==='query'){?>
		<input type="text" name="project[first_contact]" value="<?=$this->value('project/first_contact')?>" placeholder="首次接待日期" title="首次接待日期" class="date" />
<?}else{?>
		<input type="text" name="project[time_contract]" value="<?=$this->value('project/time_contract')?>" placeholder="立案日期" title="立案日期" class="date" <? if(in_array('在办',$tags))echo 'disabled';?> />
		-
		<input type="text" name="project[end]" value="<?=$this->value('project/end')?>" placeholder="预估结案日期" title="预估结案日期" class="date" <? if(in_array('在办',$tags))echo 'disabled';?> />
<?}?>
		<span><?=$this->value('meta/案源类型')?></span>
		<span>案源系数：<?=round($this->value('meta/案源系数')*100,2)?>%</span>
	</div>

	<div class="item" name="client">
		<div class="title"><label>客户及相关人：</label>
<? if(isset($people_roles[$this->user->id]) && in_subarray('督办人', $people_roles[$this->user->id], 'role')!==false && !in_array('客户已锁定',$tags)){?>
			<button type="submit" name="submit[lock_client]">锁定</button>
<? }?>
<? if(isset($people_roles[$this->user->id]) && in_subarray('督办人', $people_roles[$this->user->id], 'role')!==false && in_array('客户已锁定',$tags)){ ?>
			<button type="submit" name="submit[unlock_client]">解锁</button>
<? } ?>
		</div>

		<?=$client_list?>

		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="hidden" name="case_client[client]" class="tagging" data-placeholder="名称" data-ajax="/people/match/" />

			<select name="case_client[role]" class="chosen allow-new" data-placeholder="本案地位">
				<?=options($this->cases->getRelatedRoles($tags),$this->value('case_client/role'),'',false,false,false);?>
			</select>

			<button type="submit" name="submit[client]">添加</button>
		</span>
	 </div>

<? if(in_array('争议',$tags)){?>
	<div class="item">
		<div class="title"><label>争议焦点：</label></div>
		<input name="project[focus]" type="text" value="<?=$this->value('project/focus')?>" style="width:99%;font-size:1.2em;" />
	</div>
<? }?>

<? if(in_array('非争议',$tags)){?>
	<div class="item">
		<div class="title"><label>案件标的：</label></div>
		<input name="project[focus]" type="text" value="<?=$this->value('project/focus')?>" style="width:99%;font-size:1.2em;" />
	</div>
<? }?>
	
	<div class="item" name="staff"<?if(in_array('职员已锁定',$tags)){?> locked="locked"<?}?>>
		<div class="title"><label>律师：</label>
<?if(isset($people_roles[$this->user->id]) && in_subarray('督办人', $people_roles[$this->user->id], 'role')!==false && !in_array('职员已锁定',$tags)){?>
			<button type="submit" name="submit[lock_staff]">锁定</button>
<? }?>
<? if(isset($people_roles[$this->user->id]) && in_subarray('督办人', $people_roles[$this->user->id], 'role')!==false && in_array('职员已锁定',$tags)){ ?>
			<button type="submit" name="submit[unlock_staff]">解锁</button>
<? } ?>
		</div>

		<?=$staff_list?>

		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="hidden" name="staff[id]" class="tagging" data-placeholder="姓名" data-ajax='/staff/match/'>
			<select name="staff[role]" class="chosen allow-new" data-placeholder="本案职务">
				<?=options($this->cases->getRelatedRoles($tags),$this->value('staff/role'),'',false,false,false);?>
			</select>
			<input type="text" name="staff[weight]" value="<?=$this->value('staff/weight')?>" placeholder="占比%" />
			<button type="submit" name="submit[staff]">添加</button>
		</span>
	</div>

	<div class="item" name="account">
		<div class="title">
			<label>资金：</label>
			
<? if((isset($people_roles[$this->user->id]) && in_subarray('督办人', $people_roles[$this->user->id], 'role')!==false || $this->user->isLogged('finance')) && !in_array('费用已锁定',$tags)){?>
			<button type="submit" name="submit[lock_fee]">锁定</button>
<? }?>
<? if((isset($people_roles[$this->user->id]) && in_subarray('督办人', $people_roles[$this->user->id], 'role')!==false || $this->user->isLogged('finance')) && in_array('费用已锁定',$tags)){ ?>
			<button type="submit" name="submit[unlock_fee]">解锁</button>
<? } ?>
		</div>

		<?=$fee_list?>	
		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<select name="account[type]" class="tagging allow-new">
				<?=options(array('固定','风险','计时预付','律师服务费'),$this->value('account/type'),'类型');?>
			</select>
			<input type="text" name="account[account]" value="<?=$this->value('account/account')?>" placeholder="帐目编号" />
			<input type="text" name="account[amount]" value="<?=$this->value('account/amount')?>" placeholder="数额" />
<?if($this->user->isLogged('finance')){?>
			<label><input type="checkbox" value="1" name="account[received]">已到帐</label>
<?}?>
			<input type="text" name="account[date]" value="<?=$this->value('account/date')?>" placeholder="预估日期" class="date" />
			<input type="hidden" name="account[people]" data-ajax="/people/match/" data-placeholder="收款/付款人" class="tagging">
			<input type="text" name="account[comment]" value="<?=$this->value('account/comment');?>" placeholder="条件/备注" />
			<button type="submit" name="submit[account]">添加</button>
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
			<select name="document_tags[]" class="chosen allow-new" data-placeholder="标签" multiple="multiple" style="width:200px;height:15px;">
				<?=options($this->document->getAllTags(),$this->value('document_tags'));?>
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
