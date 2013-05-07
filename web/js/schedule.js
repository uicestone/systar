$(function(){
	$.each([page,aside],function(){
		$(this).on('sectioncreate','section[hash^="schedule"]',function(){
			var that=this;

			$(this).on('click','.portlet',function(){
				var options={id:$(this).attr('event-id'),target:this};

				if($(that).is('[hash="schedule/taskboard"]')){
					options.taskboard=that;
				}

				$.viewSchedule(options);
			});

			$(this).on('click','.portlet-header .ui-icon',function(event){
				event.stopPropagation();
				$(this).toggleClass( 'ui-icon-minusthick' ).toggleClass( 'ui-icon-plusthick' );
				$(this).parents( '.portlet:first' ).find( '.portlet-content' ).toggle();
			});
		});
	});
	
	page.on('sectionload','section',function(){
		if(controller==='schedule' && method==='lists'){
			/*日程excel导出按钮*/
			$(this).find('[name="export-excel"]').click(function(){
				window.open($.changeUrlPar(hash,'export','excel'));
			});
		}
		
		if(controller==='schedule' && method==='taskboard'){
			$(this).find( ".sortable.column" ).sortable({
				connectWith: ".sortable.column",
				stop:function(event,ui){
					var taskSort=[];
					$(".sortable.column").each(function(){
						taskSort.push($(this).sortable('toArray',{attribute:'event-id'}));
					});
					$.post('/schedule/settaskboardsort',{sortData:taskSort});
				},
				receive:function(event,ui){
					var senderId=ui.sender.attr('event-id');
					if(senderId){
						$.post('/schedule/writecalendar/update/'+senderId,{in_todo_list:0},function(){
							ui.sender.hide();
						});
					}
				}
			}).disableSelection();
			
			$(this).find('button[name="clear-completed"]').on('click',function(){
				$.get('/schedule/removetaskboardcompleted',function(){
					$.refresh('schedule/taskboard');
				});
			});
		}
	});
	
	aside.on('sectionload','section',function(){
		$(this).find('.draggable.portlet').draggable({
			helper: 'clone',
			connectToSortable:'.sortable.column',
			zIndex:10
		});
	});
});