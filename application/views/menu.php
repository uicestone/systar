<header>
	<ul id="tabs"></ul>
	<img class="throbber hidden" src="images/spinner.png" />
<? if($this->user->isLogged()){?>
	<div id="topMenu">
		<a href="/#user/profile"><?=$this->user->name?></a>
		<a href="/logout" target="_top">退出</a>
	</div>
<?}?>
</header>
