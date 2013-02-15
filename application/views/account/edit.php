<form name="account" id="<?=$this->account->id?>">
<div class="contentTableMenu">
	<div class="right">
		<button type="submit" name="submit[account]">保存</button>
		<button type="submit" name="submit[cancel]">关闭</button>
	</div>
</div>
<div class="contentTableBox">
	<div class="contentTable">
		<div class="item">
			<div class="title"><label>基本信息：</label></div>
			<input type="text" name="account[name]" value="<?=$this->value('account/name'); ?>" placeholder="摘要" title="摘要" />
			<label>￥<input type="text" name="account[amount]" value="<?=abs($this->value('account/amount')); ?>" placeholder="数额" title="数额" /></label>
			<?=radio(array('in'=>'入','out'=>'出'), 'account[way]', $this->value('account/amount')>=0?'in':'out', true)?>
			<input type="text" name="account[date]" value="<?=$this->value('account/date')?>" class="date" placeholder="日期" title="日期" />
		</div>

		<div class="item" name="related">
			<div class="title"><label>关联：</label></div>
			<input type="text" name="client[name]" value="<?=$this->value('client/name');?>" autocomplete-model="client" placeholder="客户" title="客户" />
			<input name="account[people]" class="hidden" />
			<? if(!empty($case_fee_array)){?>
			<select name="account[case_fee]">
				<?=options($case_fee_array,$this->value('account/case_fee'),'应收帐款')?>
			</select>
			<? }?>
		</div>

		<div class="item">
			<div class="title"><label>备注：</label></div>
			<textarea name="account[comment]"><?=$this->value('account/comment')?></textarea>
		</div>

		<div class="submit">
			<button type="submit" name="submit[account]">保存</button>
			<button type="submit" name="submit[cancel]">关闭</button>
		</div>
	</div>
</div>
</form>
<?=javascript('account_edit')?>