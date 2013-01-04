<form>
	<div id="loginForm">
		<div class="item">
			<input type="text" id="username" name="username" placeholder="用户名" />
		</div>
		<div class="item">
			<input name="password" type="password" id="password" placeholder="密码" />
		</div>
		<div class="submit"><input type="submit" name="submit[login]" value="登录" /></div>
	</div>
</form>
<?$this->inner_js.="affair='登录';$('#username').focus();"?>