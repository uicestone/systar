<?foreach($dialogs as $dialog){?>
<div class="message-dialog-list-item<?if(!$dialog['read']){?> unread<?}?>" id="<?=$dialog['id']?>">
	<p class="title"><?=$dialog['title']?></p>
	<span class="author"><?=$dialog['last_message_author_name']?>：</span>
	<?=$dialog['last_message_content']?>
<?	if($dialog['last_message_documents']){?>
	<p><label>附件：</label>
<?		foreach($dialog['last_message_documents'] as $document){?>
		<a href="/document/download/<?=$document['id']?>"><?=$document['name']?></a>
<?		}?>
	</p>
<?	}?>
	<p class="time"><?=date('Y-m-d H:i:s',$dialog['last_message_time'])?></p>
</div>
<hr />
<?}?>
<script type="text/javascript">
$(function(){
	var section = page.children('section[hash="'+hash+'"]');
	section.find('.message-dialog-list-item').click(function(){
		syssh.navigate('message/content/'+$(this).attr('id'),true);
	});
});
</script>