<?php foreach($messages as $message){ ?>
<div id="<?=$message['id']?>" class="message-content-list-item<?php if(!$message['read']){ ?> unread<?php } ?>">
	<span id="delete" class="icon-close hidden right"></span>
	<p class="time right"><?=date('Y-m-d H:i:s',$message['time'])?></p>
	<?php if($message['author_name']){ ?><span class="author"><?=$message['author_name']?>：</span><?php } ?>
	<hr>
	<div class="content"><?=$message['content']?></div>
<?php	if($message['documents']){ ?>
<p><label>附件：</label>
<?php		foreach($message['documents'] as $document){ ?>
	<a href="/document/download/<?=$document['id']?>"><?=$document['name']?></a>
<?php		}?>
</p>
<?php	}?>
</div>
<hr />
<?php } ?>
&nbsp;
