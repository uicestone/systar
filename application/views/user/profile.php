<form>
	<div class="contentTableMenu">
		<button type="submit" name="submit[profile]">保存</button>
		<button type="submit" name="submit[cancel]">关闭</button>
	</div>
	<div class="contentTableBox">
		<div class="contentTable">
			<div class="item">
				<div class="title"><label>设置用户名密码</label></div>
				<input type="text" name="user[username]" value="<?=$this->value('user/username')?>" placeholder="用户名" />
				<input type="password" name="user[password]" placeholder="密码" />
				<input type="password" name="user[password_new]" placeholder="新密码" />
				<input type="password" name="user[password_new_confirm]" placeholder="新密码确认" />
			</div>

			<div class="submit">
				<button type="submit" name="submit[profile]">保存</button>
				<button type="submit" name="submit[cancel]">关闭</button>
			</div>
		</div>
	</div>
</form>