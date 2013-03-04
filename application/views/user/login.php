<script type="text/javascript">
$(function(){
	$(':input[placeholder]').placeholder();
});
</script>
<form method="post">
	<div class="login-form">
<?if(isset($warning)){?>
		<span class="message ui-corner-all ui-state-error" title="点击隐藏提示">
			<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
			<?=$warning?>
		</span>
<?}?>
		<div class="item">
			<input type="text" id="username" name="username" placeholder="用户名" />
		</div>
		<div class="item">
			<input name="password" type="password" id="password" placeholder="密码" />
		</div>
		<div class="submit"><button type="submit" name="login">登录</button></div>
	</div>
</form>
