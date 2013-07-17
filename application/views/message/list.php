<?foreach($messages as $message){?>
<div id="<?=$message['id']?>" class="message-content-list-item<?if(!$message['read']){?> unread<?}?>">
	<span id="delete" class="icon-close hidden right"></span>
	<p class="time right"><?=date('Y-m-d H:i:s',$message['time'])?></p>
	<?if($message['author_name']){?><span class="author"><?=$message['author_name']?>：</span><?}?>
	<?=$message['content']?>
<?	if($message['documents']){?>
<p><label>附件：</label>
<?		foreach($message['documents'] as $document){?>
	<a href="/document/download/<?=$document['id']?>"><?=$document['name']?></a>
<?		}?>
</p>
<?	}?>
</div>
<hr />
<?}?>
&nbsp;
