<div class="contentTableBox">
<?foreach($messages as $message){?>
	<div class="message-dialog-list-item">
		<span class="author"><?=$message['author_name']?>：</span>
		<?=$message['content']?>
		<p class="time"><?=date('Y-m-d H:i:s',$message['time'])?></p>
	</div>
	<hr />
<?}?>
</div>