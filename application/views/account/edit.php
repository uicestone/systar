<form name="account" id="<?=$this->account->id?>">
	<div class="item">
		<div class="title"><label>基本信息：</label></div>
		<input type="text" name="account[name]" value="<?=$this->value('account/name'); ?>" placeholder="摘要" title="摘要" />
		<input type="text" name="account[subject]" value="<?=$this->value('account/subject')?>" placeholder="科目" title="科目" />
		<input type="text" name="account[type]" value="<?=$this->value('account/type')?>" placeholder="类型" title="类型" />
		<select name="account[received]">
			<?=options(array(0=>'预计',1=>'实际'),$account['received'],'预计/实际',true)?>
		</select>
		<label>￥<input type="text" name="account[amount]" value="<?=abs($this->value('account/amount')); ?>" placeholder="数额" title="数额" /></label>
		<?=radio(array('in'=>'入','out'=>'出'), 'account[way]', $this->value('account/amount')>=0?'in':'out', true)?>
		<input type="text" name="account[date]" value="<?=$this->value('account/date')?>" class="date" placeholder="日期" title="日期" />
		<input type="text" name="account[account]" value="<?=$this->value('account/account')?>" placeholder="账目编号" title="账目编号" />
	</div>
	
	<div class="item">
		<div class="title"><label>项目 付款/收款人：</label></div>
		<select name="account[project]" class="chosen allow-new" data-placeholder="项目">
			<?=options($this->project->getArray(), $this->value('account/project'), '', true,false,false)?>
		</select>
		<select name="account[people]" class="chosen allow-new" data-placeholder="付款/收款人">
			<?=options($this->people->getArray(), $this->value('account/people'), '', true,false,false)?>
		</select>
	</div>

	<div class="item">
		<div class="title"><label>备注：</label></div>
		<textarea name="account[comment]"><?=$this->value('account/comment')?></textarea>
	</div>
</form>