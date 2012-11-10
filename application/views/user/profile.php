<script type="text/javascript">
affair='用户资料';
$(function(){
	$('form').submit(function(){
		if($('input[name="user[password_new]"]').val()!=$('input[name="user_extra[password_new_confirm]"]').val()){
			showMessage('两次新密码输入不一致','warning');
			return false;
		}
	});
});
</script>
<div class="contentTableBox">
	<div class="contentTable">
	<form method="post">
		<div class="item">
			<div class="title"><label>设置用户名密码</label></div>
		</div>
		
		<?if($this->config->item('ucenter')){?>
		<div class="item">
			<div class="title"><label>用户名：</label></div>
			<input type="text" name="user[username]" value="<? displayPost('user/username')?>" />
		</div>
		<?}?>

		<div class="item">
			<div class="title"><label>旧密码：</label></div>
			<input type="password" name="user_extra[password]" />
		</div>
		
		<div class="item">
			<div class="title"><label>新密码：</label></div>
			<input type="password" name="user[password_new]" />
		</div>

		<div class="item">
			<div class="title"><label>确认：</label></div>
			<input type="password" name="user_extra[password_new_confirm]" />
		</div>
		<div class="submit">
			<input type="submit" name="submit[profile]" value="保存" />
		</div>
	</form>
	</div>
</div>