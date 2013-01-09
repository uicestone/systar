<div id="topBar">
<? if(!$this->as_popup_window && $this->user->isLogged()){?>
	<div id="topMenu">
		<a href="/user/profile"><?=$this->user->name?></a>
<?	if($this->company->ucenter){?>
		<a href="http://www.lawyerstars.com/home.php?mod=space&do=pm" target="_blank"><img src="/images/message.png" />
<?	}?>
<?	if($this->company->ucenter && $this->user->new_messages>0){?>
		<?=$this->user->new_messages?>
<?	}?>
		<a href="#logout" target="_top">退出</a>
<?	if($this->company->ucenter){ ?>
		<a href="javascript:showWindow('schedule/add?case=598')" style="font-size:10px;color:#DDD;">反馈</a>
<?	}?>
	</div>
<?}?>
</div>
