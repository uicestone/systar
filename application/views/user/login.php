<script>
affair='登录';
$(function(){
	$('#username').focus();
});
</script>
<div id="loginForm">
<form method="post">
    <div class="item">
        <input type="text" id="username" name="username" placeholder="用户名" />
    </div>
    <div class="item">
        <input name="password" type="password" id="password" placeholder="密码" />
    </div>
    <div class="submit"><input type="submit" name="submit[login]" value="登录" /></div>
</form>
</div>
