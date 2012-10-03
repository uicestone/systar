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
        <? if(post('news/uid')==$_SESSION['id']){?>
        <input type="submit" name="submit[news]" value="保存" />
        <? }?>
        <input type="submit" name="submit[cancel]" value="关闭" />
    </div>
</div>
<div class="contentTableBox">
	<div class="contentTable">
		<div class="item">
			<div class="title"><label>标题：</label></div>
			<input name="news[title]" value="<? displayPost('news/title');?>" type="text" />
		</div>

		<div class="item">
			<div class="title"><label>内容：</label></div>
	<? if(post('news/uid')==$_SESSION['id']){?>
			<textarea name="news[content]" rows="10"><? displayPost('news/content'); ?></textarea>
	<? }else{?>
			<div class="content"><? displayPost('news/content'); ?></div>
	<? }?>
		</div>

		<div class="submit">
	<? if(post('news/uid')==$_SESSION['id']){?>
			<input type="submit" name="submit[news]" value="保存" />
	<? }?>
			<input type="submit" name="submit[cancel]" value="关闭" />
		</div>
	</div>
</div>
</form>