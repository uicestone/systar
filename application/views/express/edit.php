<form method="post">
<div class="contentTableMenu">
	<div class="right">
		<input type="submit" name="submit[express]" value="保存" />
		<input type="submit" name="submit[cancel]" value="取消" />
	</div>
</div>
<div class="contentTableBox">
	<div class="contentTable">

		<div class="item">
			<div class="title"><label>寄送人：</label></div>
			<input type="text" name="express_extra[sender_name]" value="<?=post('express_extra/sender_name'); ?>" />
		</div>

		<div class="item">
			<div class="title"><label>寄送地点：</label></div>
			<input type="text" name="express[destination]" value="<?=post('express/destination'); ?>" />
		</div>

		<div class="item">
			<div class="title"><label>寄送时间：</label></div>
			<input type="text" name="express_extra[time_send]" value="<?=post('express_extra/time_send'); ?>" class="date" />
		</div>

		<div class="item">
			<div class="title"><label>寄送内容：</label></div>
			<input type="text" name="express[content]" value="<?=post('express/content'); ?>" />
		</div>

		<div class="item">
			<div class="title"><label>寄送数目：</label></div>
			<input type="text" name="express[amount]" value="<?=post('express/amount'); ?>" />
		</div>

		<div class="item">
			<div class="title"><label>单号：</label></div>
			<textarea name="express[num]"><?=post('express/num'); ?></textarea>
		</div>

		<div class="item">
			<div class="title"><label>备注：</label></div>
			<textarea name="express[comment]"><?=post('express/comment'); ?></textarea>
		</div>

		<div class="submit">
			<input type="submit" name="submit[express]" value="保存" />
			<input type="submit" name="submit[cancel]" value="取消" />
		</div>
	</div>
</div>
</form>