<script type="text/javascript" src="js/jHtmlArea/jHtmlArea-0.7.0.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('textarea').htmlarea({css:"js/jHtmlArea/style/jHtmlArea.Editor.css"});
});
</script>
<link rel="stylesheet" type="text/css" href="js/jHtmlArea/style/jHtmlArea.css" />

<form method="post">
<div class="contentTableMenu">
    <div class="right">
        <? if($this->value('news/uid')==$this->user->id){?>
        <button type="submit" name="submit[news]">保存</button>
        <? }?>
    </div>
</div>
<div class="contentTableBox">
	<div class="contentTable">
		<div class="item">
			<div class="title"><label>标题：</label></div>
			<input name="news[title]" value="<?=$this->value('news/title');?>" type="text" />
		</div>

		<div class="item">
			<div class="title"><label>内容：</label></div>
	<? if($this->value('news/uid')==$this->user->id){?>
			<textarea name="news[content]" rows="10"><?=$this->value('news/content'); ?></textarea>
	<? }else{?>
			<div class="content"><?=$this->value('news/content'); ?></div>
	<? }?>
		</div>

		<div class="submit">
	<? if($this->value('news/uid')==$this->user->id){?>
			<button type="submit" name="submit[news]">保存</button>
	<? }?>
		</div>
	</div>
</div>
</form>