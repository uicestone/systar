<div id="topBar">
<? if(!$this->config->item('as_popup_window') && is_logged()){?>
	<div id="topMenu">
		<a href="user?profile"><? echo array_dir('_SESSION/username');?></a>
		<?if($this->config->item('ucenter')){?>
		<a href="http://www.lawyerstars.com/home.php?mod=space&do=pm" target="_blank"><img src="/images/message.png" />
		<?}?>
		<?if(array_dir('_SESSION/new_messages')>0){?>
			<?echo array_dir('_SESSION/new_messages')?>
		<?}?>
		</a>
		<a href="user/logout" target="_top">退出</a>
		<?if($this->config->item('ucenter')){ ?>
		<a href="javascript:showWindow('schedule?add&case=598')" style="font-size:10px;color:#DDD;">反馈</a>
		<?}?>
	</div>
<?}?>
</div>
