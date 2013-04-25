<div class="column">	
	<div class="portlet draggable ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" style="display:none">
		<div class="portlet-header ui-widget-header ui-corner-all ellipsis">
			<span class='ui-icon ui-icon-minusthick'></span>
		</div>
		<div class="portlet-content"></div>
	</div>
<? foreach ($side_task_board as $task) { ?>
	<div class="portlet draggable ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" event-id="<?=$task['id'] ?>">
		<div class="portlet-header ui-widget-header ui-corner-all ellipsis">
			<span class='ui-icon ui-icon-minusthick'></span>
			<?=$task['name'] ?>
		</div>
		<div class="portlet-content"><?=$task['content'] ?></div>
	</div>
<? } ?>
</div>
<script type="text/javascript">
$(function(){
	var side=aside.children('section[for="'+hash+'"]');
	side.find('.draggable.portlet').draggable({
		helper: 'clone'
	});
});
</script>
<?=javascript('schedule_list')?>