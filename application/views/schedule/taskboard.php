<style>
	.column { width: 170px; float: left; padding-bottom: 100px; }
	.portlet { margin: 0 1em 1em 0; }
	.portlet-header { margin: 0.3em; padding-bottom: 4px; padding-left: 0.2em; }
	.portlet-header .ui-icon { float: right; }
	.portlet-content { padding: 0.4em; }
	.ui-sortable-placeholder { border: 1px dotted black; visibility: visible !important; height: 50px !important; }
	.ui-sortable-placeholder * { visibility: hidden; }
</style>
<script>
	$(function() {
		$( ".column" ).sortable({
			connectWith: ".column",
			stop:function(event,ui){
				var taskSort=[];
				$( ".column").each(function(){
					taskSort.push($(this).sortable( "toArray"));
				});
				$.post('/schedule/settaskboardsort',{sortData:taskSort},function(result){
					console.log(result);
				});
			}
		});
 
		$( ".portlet" ).click(function(){
				$('#taskboard').showSchedule($(this).attr('id').replace('task_',''));
			})
			.addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
			.find( ".portlet-header" )
				.addClass( "ui-widget-header ui-corner-all" )
				.prepend( "<span class='ui-icon ui-icon-minusthick'></span>")
				.end()
			.find( ".portlet-content" );
 
		$( ".portlet-header .ui-icon" ).click(function() {
			$( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
			$( this ).parents( ".portlet:first" ).find( ".portlet-content" ).toggle();
		});
 
		$( ".column" ).disableSelection();

		$('input[name="add[task]"]').click(function(){
			$("#taskboard").createSchedule();
		})
	});
	</script>
<div class="contentTableMenu">
	<div class="right">
		<input type="button" name="add[task]" value="添加" />
	</div>
</div>
<!--.contentTableMenu是和.contentTableBox平级的 TODO: xiuzhi uice 11/26-->
<div id="taskboard" class="contentTableBox">
<!--标准的写法应该是php判断、循环部分的缩进和html的缩进分开处理，也就是说这里应该顶格 TODO: xiuzhi uice 11/26-->
<?foreach($task_board as $column){?>
	<div class="column">
	<!--这里的\<\?应该顶格，但foreach应该空一个tab，因为是上面foreach的内层 TODO: xiuzhi uice 11/26-->
<?	foreach($column as $task){?>
	<!--html的缩进参照上面的html，这里应该比.column缩进一个tab，此处是两个空格 TODO: xiuzhi uice 11/26-->
		<div class="portlet" id="task_<?=$task['id']?>">
			<div class="portlet-header"><?=$task['title']?></div>
			<div class="portlet-content"><?=$task['content']?></div>
		</div>
<?	}?>
	</div>
<?}?>
</div>