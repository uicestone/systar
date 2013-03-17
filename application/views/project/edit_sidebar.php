<? if(($this->user->isLogged('manager') || $responsible_partner==$this->user->id) && !in_array('立案审核',$labels) && !in_array('咨询',$labels)){?>
		<button type="submit" name="submit[review]" class="major">立案审核</button>
<? }//TODO: 批量替换多余的空格?>
<? if($responsible_partner!=$this->user->id && !in_array('客户锁定',$labels) && in_array('立案审核',$labels)){?>
		<button type="submit" name="submit[apply_lock]" class="major">申请锁定</button>
<? }?>
<? if($this->user->isLogged('finance') && in_array('申请归档',$labels) && !in_array('财务审核',$labels)){?>
		<button type="submit" name="submit[review_finance]" class="major">财务审核</button>
<? }?>
<? if($this->user->isLogged('admin') && in_array('申请归档',$labels) && !in_array('信息审核',$labels)){?>
		<button type="submit" name="submit[review_info]" class="major">信息审核</button>
<? }?>
<? if($this->user->isLogged('manager') && in_array('申请归档',$labels) && !in_array('主管审核',$labels)){?>
		<button type="submit" name="submit[review_manager]" class="major">主管审核</button>
<? }?>
<? if($this->user->isLogged('admin') && in_array('申请归档',$labels) && in_array('财务审核',$labels) && in_array('信息审核',$labels) && in_array('主管审核',$labels) && !in_array('案卷归档',$labels)){?>
		<button type="submit" name="submit[file]" class="major">实体归档</button>
<? }?>
<? if(in_array('咨询',$labels)){ ?>
		<button type="submit" name="submit[new_case]" class="major">立案</button>
		<button type="submit" name="submit[file]" class="major">归档</button>
<? } ?>
<?if(!in_array('申请归档',$labels) && !in_array('案卷归档',$labels) &&
	in_array('立案审核',$labels) && 
	in_array('类型锁定',$labels) && 
	in_array('客户锁定',$labels) &&
	in_array('职员锁定',$labels) &&
	in_array('费用锁定',$labels)
){?>
		<button type="submit" name="submit[apply_file]" class="major">申请归档</button>
<? }?>
		<button type="submit" name="submit[project]" class="major">保存</button>

<select id="labels" data-placeholder="标签" multiple="multiple" style="width:239px;">
	<?=options($this->project->getAllLabels(),$labels)?>
</select>