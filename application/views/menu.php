<header>
	<ul id="tabs"></ul>
	<img class="throbber hidden" src="images/spinner.png" />
<?if($this->user->isLogged()){?>
	<div id="topMenu">
		
<?	if($this->user->isLogged('candidate') && $this->company->syscode=='shdfz'){?>
		<a href="http://zs.shdfz.net/">返回上大附中 - 招生网</a>
<?	}?>
		<a href="/#profile"><?=$this->user->name?></a>
		<a href="/logout" target="_top">退出</a>
	</div>
<?}?>
</header>
