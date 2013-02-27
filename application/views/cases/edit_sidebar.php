<? if($responsible_partner==$this->user->id && !$cases['is_reviewed'] && !$cases['is_query']){?>
		<button type="submit" name="submit[review]" class="major">立案审核</button>
<? }//TODO: 批量替换多余的空格?>
<? if($responsible_partner!=$this->user->id && !$cases['client_lock'] && $cases['is_reviewed']){?>
		<button type="submit" name="submit[apply_lock]" class="major">申请锁定</button>
<? }?>
<? if($this->user->isLogged('finance') && $this->value('cases/apply_file') && !$this->value('cases/finance_review')){?>
		<button type="submit" name="submit[review_finance]" class="major">财务审核</button>
<? }?>
<? if($this->user->isLogged('admin') && $this->value('cases/apply_file') && !$this->value('cases/info_review')){?>
		<button type="submit" name="submit[review_info]" class="major">信息审核</button>
<? }?>
<? if($this->user->isLogged('manager') && $this->value('cases/apply_file') && !$this->value('cases/manager_review')){?>
		<button type="submit" name="submit[review_manager]" class="major">主管审核</button>
<? }?>
<? if($this->user->isLogged('admin') && $this->value('cases/apply_file') && $this->value('cases/finance_review') && $this->value('cases/info_review') && $this->value('cases/manager_review') && !$this->value('cases/filed')){?>
		<button type="submit" name="submit[file]" class="major">实体归档</button>
<? }?>
<? if($cases['is_query']){ ?>
		<button type="submit" name="submit[new_case]" class="major">立案</button>
		<button type="submit" name="submit[file]" class="major">归档</button>
<? } ?>
<? if(!$cases['apply_file'] &&
	$cases['is_reviewed'] && 
	$cases['type_lock'] && 
	$cases['client_lock'] &&
	$cases['staff_lock'] &&
	$cases['fee_lock']
){?>
		<button type="submit" name="submit[apply_file]" class="major">申请归档</button>
<? }?>
		<button type="submit" name="submit[cases]" class="major">保存</button>

