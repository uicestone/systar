<header>
	<ul id="tabs"></ul>
	<img class="throbber hidden" src="images/spinner.png" />
	<div id="topMenu">
<?if($this->user->isLogged()){?>
		<a href="#profile"><?=$this->user->name?></a>
		<a href="#message"><img src="/images/message.png" alt="消息" /></a>
		<a href="/logout" target="_top">退出</a>
<?}else{?>
	<a href="/login">登陆</a>
<?}?>
	</div>
</header>
