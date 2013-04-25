<? foreach ($task_board as $column) { ?>
<div class="column sortable">	
<? foreach ($column as $task) { ?>
	<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" event-id="<?= $task['id'] ?>">
		<div class="portlet-header ui-widget-header ui-corner-all ellipsis">
			<span class='ui-icon ui-icon-minusthick'></span>
			<?= $task['name'] ?>
		</div>
		<div class="portlet-content"><?= $task['content'] ?></div>
	</div>
<? } ?>
</div>
<? } ?>
<div class="column sortable"></div>
<script type="text/javascript">
$(function(){
	
	var section=page.children('section[hash="'+hash+'"]');
	
	section.find( ".sortable.column" ).sortable({
		connectWith: ".sortable.column",
		stop:function(event,ui){
			var taskSort=[];
			$(".sortable.column").each(function(){
				taskSort.push($(this).sortable('toArray',{attribute:'event-id'}));
			});
			$.post('/schedule/settaskboardsort',{sortData:taskSort});
		}
	}).disableSelection();
});
</script>