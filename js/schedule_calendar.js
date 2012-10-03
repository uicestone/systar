$(function() {
	var date=new Date();
	var calendar=$('#calendar').fullCalendar({
		defaultView: 'agendaWeek',
		height: $(window).height()-25,
		titleFormat:{
			month: 'yyyy年 MMMM', 
			week: "yyyy年 MMMMd日{' - '[MMMM]d日}",
			day: 'yyyy年 MMMM d日 dddd'
		},
		firstDay:1,
		firstHour:9,
		slotMinutes:15,
		header: {
			left: 'prev,next,today',
			center: 'title',
			right: 'agendaWeek,month,agendaDay'
		},
		selectable: true,
		selectHelper: true,
		select: function(startDate,endDate, allDay) {
			var dialog=createDialog('新日程');

			$.get('misc/gethtml/schedule_calendar_add',function(schedule_calendar_add_form){

				//获取表单html
				dialog.html(schedule_calendar_add_form).find('#combobox').combobox();

				//配置type和completed默认值
				$('input[name="type"][value="0"]').attr('checked','checked');
				$('input[name="completed"][value="'+(startDate.getTime()<date.getTime()?1:0)+'"]').attr('checked','checked');
				var typeRadio = $('input[name="type"]');
				var caseSelect = $('select[name="case"]');

				//监听项目类别变化
				typeRadio.change(function(){
					var type=$('input[name="type"]:checked').val();

					caseSelect.getOptions('case_getListByScheduleType',type,1,function(scheduleCase){
						caseSelect.trigger('change',{scheduleCase:scheduleCase,type:type});
					});
				});
				
				//监听案件变化
				caseSelect.change(function(event,data){
					if(!data){
						scheduleCase = $(this).val();
					}else{
						scheduleCase = data.scheduleCase;
					}
					
					if((data && data.type==2) || $('input[name="type"]:checked').val()==2){
						$('#clientSelectBox').show(200).children('select').removeAttr('disabled')
						.getOptions('client_getListByCase',scheduleCase,1);
					}else{
						$('#clientSelectBox,#clientInputBox').hide(200).children('select').attr('disabled','disabled');
					}
				});
				typeRadio.change();
			});
			
			dialog.dialog( "option", "buttons", [
				{
					text: "保存",
					click: function() {
						if($('input[name="name"]').val()==''){
							alert('日志名称必填');
							$('input:[name="name"]').focus();
						}else{
							var start=startDate.getTime()/1000;
							var end=endDate.getTime()/1000;
							var postData=$.extend($('#schedule').serializeJSON(),{time_start:start,time_end:end,all_day:Number(allDay)});
							delete postData.type;

							$.post("schedule/writecalendar",postData,
								function(data){
									if(!isNaN(data) && data!=0){
										calendar.fullCalendar('renderEvent',
											{
												id:data,
												title:$('[name="name"]').val(),
												start: startDate,
												end: endDate,
												allDay: allDay,
												color:startDate.getTime()>date.getTime()?'#E35B00':'#36C'
											}
										);
										calendar.fullCalendar('unselect');
										dialog.dialog('close');
									}else{
										alert('添加失败，请勿关闭窗口，联系小陆');
										console.log(data);
									}
								}
							);
						}
					}
				}
			])
			.dialog('open');
		},
		
		editable: true,
		events: location.href+'/readcalendar',
		
		eventClick: function(event) {
			$.get("schedule/readcalendar/"+event.id,function(result){
				try{
					var schedule=$.parseJSON(result);
				}catch(e){
					alert('程序错误，联系小陆：'+"\n"+e+"\n"+result);
					console.log('程序错误，联系小陆：'+"\n"+e+"\n"+result);
				}
				var dialog=createDialog(schedule.name)
				.append('<p>'+schedule.time_start+' ('+schedule.hours_own+'小时)</p>')
				.append('<span>案件：'+schedule.case_name+'</span>');
				if(schedule['case']<20 && schedule['case']>=8){
					dialog.append('<span>，客户：'+schedule.client_name+'</span>');
				}
				dialog.append('<hr />');
				for(var i=0; i<schedule.content_paras.length; i++){
					dialog.append('<p>'+schedule.content_paras[i]+'</p>');
				}
				dialog.append('<hr />');
				for(var i=0; i<schedule.experience_paras.length; i++){
					dialog.append('<p>'+schedule.experience_paras[i]+'</p>');
				}
				dialog
				.dialog( "option", "buttons", [
					{
						text: "编辑",
						click: function(){
							$.get('misc/gethtml/schedule_calendar_add?edit',function(html){
								dialog.html(html);
								$('[name="name"]').val(schedule.name);
								$('[name="content"]').val(schedule.content);
								$('[name="experience"]').val(schedule.experience);
								$('[name="place"]').val(schedule.place);
								$('[name="fee"]').val(schedule.fee);
								$('[name="fee_name"]').val(schedule.fee_name);
								//$('[name="name"]').val(schedule.name);
								$('[name="completed"][value="'+schedule.completed+'"]').attr('checked','checked');

								dialog.dialog( "option", "buttons", [
									{
										text: "删除",
										click: function() {
											$.post("schedule?writecalendar",{id:event.id,action:'delete'},function(result){
												console.log(result);
											});
											$(this).dialog("close");
											calendar.fullCalendar('removeEvents',[event.id]);
										}
									},
									{
										text: "高级",
										click: function() {
											$(this).dialog("close");
											showWindow('schedule?edit='+event.id);
										}
									},
									{
										text: "保存",
										click: function() {
											$.post("schedule?writecalendar",{
												id:event.id,
												action:'updateContent',
												content:$('[name="content"]').val(),
												experience:$('[name="experience"]').val(),
												completed:$('input[name="completed"]:radio:checked').val(),
												fee:$('[name="fee"]').val(),
												fee_name:$('input[name="fee_name"]').val(),
												place:$('input[name="place"]').val()
											});
											$(this).dialog("close");
										}
									}
								]);
							});
						}
					}
				])
				.dialog('open');
			});
			
		},
		eventDrop: function(event,dayDelta,minuteDelta,allDay) {
			$.post("schedule?writecalendar",{id:event.id,action:'drag',dayDelta:dayDelta,minuteDelta:minuteDelta,allDay:Number(allDay)},function(){
				if(event.start.getTime()>date.getTime()){
					event.color='#E35B00';
				}else{
					event.color='#36C';
				}
				calendar.fullCalendar('rerenderEvents');
			});
		},
		eventResize:function(event,dayDelta,minuteDelta){
			$.post("schedule?writecalendar",{id:event.id,action:'resize',dayDelta:dayDelta,minuteDelta:minuteDelta,allDay:event.allDay},function(result){
				if(result!=''){
					alert('保存失败了，请联系程序员');
					console.log(result);
				}
			});
		}
	});
});