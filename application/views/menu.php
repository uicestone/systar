<header>
	<span style="color: #9AC;text-shadow: 1px 1px 3px #222;position:absolute;left:10px;font-size:10px" title="这是一个尚在测试期间的系统。若您在使用中发生问题，或有任何建议和意见，请发送邮件至dev@sys.sh">测试版</span>
	<ul id="tabs"></ul>
	<img class="throbber hidden" src="images/spinner.png" />
	<div id="topMenu">
<?if($this->user->isLogged()){?>
		
<?	if($this->user->isLogged('candidate') && $this->company->syscode=='shdfz'){?>
		<a href="http://zs.shdfz.net/">返回上大附中 - 招生网</a>
<?	}?>
		<a href="/#profile"><?=$this->user->name?></a>
		<a href="/logout" target="_top">退出</a>
<?}else{?>
	<a href="/login">登陆</a>
<?}?>
	</div>
</header>
