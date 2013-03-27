<div class="column sortable">	
<? foreach ($side_task_board as $task) { ?>
	<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" id="<?=$task['id'] ?>">
		<div class="portlet-header ui-widget-header ui-corner-all">
			<span class='ui-icon ui-icon-minusthick'></span>
			<?=$task['name'] ?>
		</div>
		<div class="portlet-content"><?=$task['content'] ?></div>
	</div>
<? } ?>
</div>