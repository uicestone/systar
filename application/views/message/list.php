<?foreach($messages as $message){?>
<div class="message-content-list-item<?if(!$message['read']){?> unread<?}?>">
	<p class="time right"><?=date('Y-m-d H:i:s',$message['time'])?></p>
	<span class="author"><?=$message['author_name']?>：</span>
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
<script type="text/javascript">
$(function(){
	page.on('sectionshow sectionload','section',function(){
		if(controller==='message' && method==='content'){
			window.clearInterval(polling.message);
			polling.message=window.setInterval(function(){
				$.post('/'+hash,{blocks:'content'});
			},3000);
		}
	});
});
</script>