<form method="post">
	<div class="contentTableBox">
		<div class="item">
			<div class="title"><label>新用户注册</label></div>
			<input type="text" name="username" value="<?=$this->value('user/username')?>" placeholder="用户名" />
			<input type="password" name="password" placeholder="密码" />
			<input type="password" name="password_confirm" placeholder="密码确认" />
		</div>
	</div>
</form>
