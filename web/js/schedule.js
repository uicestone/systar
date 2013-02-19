jQuery.fn.showSchedule=function(event){
	var target=$(this);
	var dialog=$('<div class="schedule"></div>').appendTo('body')
	.dialog({
		position:{
			my:'left bottom',
			at:'right top',
			of:target
		},
		dialogClass:'shadow schedule-form',
		modal:true,
		close:function(){
			$(this).remove();
		}
	})
	
	.dialog( "option", "buttons", [
		{
			text: "编辑",
			click: function(){
				$(this).editSchedule(event);
			}
		}
	]);

	if(hash=='schedule'){
		dialog.dialog('option','buttons',[{
			text:'添加至任务墙',
			click:function(){
				$.get('/schedule/addtotaskboard/'+event.id,function(response){
					$.processMessage(response);
				});
			}
		}].concat(dialog.dialog('option','buttons')));
	}else if(hash=='schedule/taskboard'){
		dialog.dialog('option','buttons',[{
			text:'移出任务墙',
			click:function(){
				$.get('/schedule/deletefromtaskboard/'+event.id,function(){
					dialog.dialog('close');
					$("#task_"+event.id).remove();
				});

			}
		}].concat(dialog.dialog('option','buttons')));
	}

	$.get("/schedule/view/"+event.id,function(response){
		dialog.dialog('option','title',response.data.title.content);
		dialog.html(response.data.content.content);
	},'json');
}

jQuery.fn.createSchedule=function(startDate, endDate, allDay, project, completed){
	date = new Date();
	
	/*日历上选中的区块*/
	var selection=$(this);
               
	var dialog=$('<div class="dialog"></div>').appendTo('body')
	.dialog({
		position:{
			my:'left bottom',
			at:'right top',
			of:selection
		},
		dialogClass:'shadow schedule-form',
		autoOpen:true,
		modal:true,
		close:function(){
			selection.parents('.fc:first').fullCalendar('unselect');
			$(this).remove();
		},
		buttons:[{
			text: "+",
			click: function(){
				dialog.append('<input name="schedule_profile_name[]" class="text" placeholder="信息名称" style="width:22%" /><input name="schedule_profile_content[]" class="text" placeholder="信息内容" style="width:70%" />');
			}
		},
		{
			text: "保存",
			click: function() {
			if(startDate && endDate){
				var content=dialog.find('[name="content"]').val();
				var project=dialog.find('[name="project"]').val();
				var people=dialog.find('[name="people"]').val();
				var completed=Number(dialog.siblings('.ui-dialog-buttonpane').find('[name="completed"]').is(':checked'));
				var paras=content.split("\n");
				var name=paras[0];
				var data={
					time_start:startDate.getTime()/1000,
					time_end:endDate.getTime()/1000,
					all_day:Number(allDay),
					content:content,
					name:name,
					"case":project,
					completed:completed
				}
				$.post("/schedule/writecalendar/add",data,function(response){
					if(response.status=='success'){
						$(calendar).fullCalendar('renderEvent',{
							id:response.data.id,
							title:response.data.name,
							start: startDate,
							end: endDate,
							allDay: allDay,
							color:startDate.getTime()>date.getTime()?'#E35B00':'#36C'
						});
						dialog.dialog('close');

					}else{
						$.showMessage('日程添加失败','warning');
					}
				},'json');
			}else{
				var content=dialog.find('[name="content"]').val();
				var paras=content.split("\n");
				var name=paras[0];
				var data={
					content:content,
					name:name
					
				}
				$.post("/schedule/writecalendar/add",data,
					function(response){
						$.get('/schedule/addtotaskboard/'+response.data.id,function(){
							$('.column:first').append(
								'<div class="portlet" id="task_'+response.data.id+'">'+
								'<div class="portlet-header">'+response.data.name+'</div>'+
								'<div class="portlet-content">'+response.data.name+'</div>'+
								'</div>'
							);
							$('.column:first')
							.find( ".portlet:last" )
							.addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
							.find( ".portlet-header" )
								.addClass( "ui-widget-header ui-corner-all" )
								.prepend( "<span class='ui-icon ui-icon-minusthick'></span>")
								.end();

							dialog.dialog('close');
						});
					},'json');
				}
			}
		}]
	});
	
	var buttonCheckbox=$('<div class="ui-dialog-buttonset" style="float:left;padding:.5em .4em"><input type="checkbox" id="completed" name="completed" text-checked="已完成" text-unchecked="未完成" /><label for="completed" ></label></div>')
	.appendTo(dialog.siblings('.ui-dialog-buttonpane'))
	.find('#completed').button();
	
	if(startDate<date){
		buttonCheckbox.attr('checked','checked');
	}
	
	buttonCheckbox.trigger('change');
	
	$.get('/schedule/add',function(response){
		dialog.dialog('option','title',response.data.name.content)
		.html(response.data.content.content).trigger('blockload')
		.find('[name="content"]').focus();
		
		dialog.on('autocompleteselect','[name="project_name"]',function(event,data){
			$(this).siblings('[name="project"]').val(data.value);
		})
		.on('autocompleteresponse','[name="project_name"]',function(event,data){
			$(this).siblings('[name="project"]').val('');
		});
	},'json');
}

jQuery.fn.editSchedule=function(event){
	dialog=$(this);
	
	$.get('/schedule/edit/'+event.id,function(response){
		dialog.dialog('option','title',response.data.title.content).html(response.data.content.content).find('[name="name"]').focus();
		
		dialog.dialog('option','buttons',[
			{
				text: "+",
				click: function(){
					;
				}
			},
			{
				text: "删除",
				click: function() {
					$.get("/schedule/writecalendar/delete/"+event.id,function(result){
					});
					$(this).dialog("close");
					if($('#page .contentTableBox').attr('id')=='calendar'){
						$(calendar).fullCalendar('removeEvents',[event.id]);
						
					}else if($('#page .contentTableBox').attr('id')=='taskboard'){
						$("#task_"+event.id).remove();
					}
				}
			},
			{
				text: "保存",
				click: function() {
					var content=dialog.find('[name="content"]').val();
					var paras=content.split("\n");
					var name=paras[0];
					var data={
						content:content,
						name:name
					}
					$.post("/schedule/writecalendar/update/"+event.id,data,function(){
						event.title=data.name;
						if(event.start){
							$(calendar).fullCalendar('updateEvent',event);
						}else{
							//TODO 更新任务板上的任务时，标题无法刷新。主要是不知道怎么用选择器。因为InnerHTML有一个加号
							$('.portlet#task_'+event.id+' .portlet-content').html(event.title);
						}
						dialog.dialog('close');
					});
				}
			}
		]);
	},'json');
}