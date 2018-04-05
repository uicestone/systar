<div class="contentTableMenu">
	<button type="button" name="clear-completed" title="将已完成的任务移出任务墙">清除已完成</button>
</div>
<? foreach ($task_board as $column) { ?>
<div class="column sortable">	
<? foreach ($column as $task) { ?>
	<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all<?php if(!$task['completed']){ ?> todo<?php } ?><?php if(!$task['completed'] && isset($task['start']) && $task['start']<$this->date->now){ ?> expired<?php } ?>" event-id="<?=$task['id']?>">
		<div class="portlet-header ui-widget-header ui-corner-all ellipsis">
			<span class='ui-icon ui-icon-minusthick'></span>
			<?=$task['name']?>
		</div>
		<div class="portlet-content"><?=str_getSummary($task['content'],60)?>
<?	if(isset($task['project'])){ ?>
			<hr /><span class="project">事务：<?=$task['project_name']?></span>
<?php } ?>
		</div>
	</div>
<? } ?>
</div>
<? } ?>
<div class="column sortable"></div>