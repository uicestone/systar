<?foreach($dialogs as $dialog){ ?>
<div class="message-dialog-list-item<?php if(!$dialog['read']){ ?> unread<?php } ?>" id="<?=$dialog['id']?>">
	<span id="delete" class="icon-close right hidden"></span>
	<p class="title"><?=$dialog['title']?></p>
	<?php if($dialog['last_message_author_name']){ ?><span class="author"><?=$dialog['last_message_author_name']?>：</span><?php } ?>
	<?=$dialog['last_message_content']?>
<?	if($dialog['last_message_documents']){ ?>
	<p><label>附件：</label>
<?		foreach($dialog['last_message_documents'] as $document){ ?>
		<a href="/document/download/<?=$document['id']?>"><?=$document['name']?></a>
<?		}?>
	</p>
<?	}?>
	<p class="time"><?=date('Y-m-d H:i:s',$dialog['last_message_time'])?></p>
</div>
<hr />
<?php } ?>
&nbsp;
