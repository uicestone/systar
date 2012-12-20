<form method="post">
<div class="contentTableMenu">
	<div class="right">
		<input type="submit" name="submit[account]" value="保存" />
		<input type="submit" name="submit[cancel]" value="关闭" />
	</div>
</div>
<div class="contentTableBox">
	<div class="contentTable">
		<div class="item">
			<div class="title"><label>名目：</label></div>
			<input name="account[name]" value="<?=$this->value('account/name'); ?>" type="text" />
		</div>

		<div class="item">
			<div class="title"><label>数额：</label></div>
			<label><input type="radio" name="account_extra[type]" value="0" checked="checked" />入</label>
			<label><input type="radio" name="account_extra[type]" value="1" />出</label>
			<label>￥<input type="text" name="account[amount]" value="<?=$this->value('account/amount'); ?>" style="width:88%" /></label>
		</div>

		<div class="item">
			<div class="title"><label>时间：</label></div>
				<input name="account_extra[time_occur]" value="<?=$this->value('account_extra/time_occur')?>" type="text" class="date" />
		</div>

		<div class="item">
			<div class="title"><label>客户：</label></div>
			<? if(isset($case_client_array)){?>
			<select name="account[client]">
				<?=options($case_client_array,$this->value('account/client'),true)?>
			</select>
			<? }else{?>
			<input type="text" name="account_extra[client_name]" value="<?=$this->value('account_extra/client_name');?>" autocomplete="client" style="width:90%" />
			<input type="submit" name="submit[recognizeOldClient]" value="识别" />
			<? }?>
		</div>

		<div class="item">
			<div class="title"><label>收费：</label></div>
			<? if(!empty($case_fee_array)){?>
			<select name="account[case_fee]">
				<?=options($case_fee_array,$this->value('account/case_fee'),true)?>
			</select>
			<? }?>
		</div>

		<div class="item">
			<div class="title"><label>备注：</label></div>
			<textarea name="account[comment]"><?=$this->value('account/comment')?></textarea>
		</div>

		<div class="submit">
			<input class="submit" type="submit" name="submit[account]" value="保存">
			<input class="submit" type="submit" name="submit[cancel]" value="关闭">
		</div>
	</div>
</div>
</form>