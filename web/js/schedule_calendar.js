$(function() {
	var calendar=$('#calendar').fullCalendar({
		defaultView: 'agendaWeek',
		height: $(window).height()-25,
		titleFormat:{
			month: 'yyyy年 MMMM', 
			week: "yyyy年 MMMMd日{' - '[MMMM]d日}",
			day: 'yyyy年 MMMM d日 dddd'
		},
		columnFormat:{
			month: 'ddd',
			week: 'ddd M/d',
			day: 'dddd M/d'
		},
		buttonText:{
			prev:'&nbsp;<&nbsp;',  // left triangle
			next:'&nbsp;>&nbsp;',  // right triangle
			prevYear:'&nbsp;&lt;&lt;&nbsp;', // <<
			nextYear:'&nbsp;&gt;&gt;&nbsp;', // >>
			today:'今天',
			month:'月',
			week:'周',
			day:'日'
		},
		monthNames:['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'],
		monthNamesShort:this.monthNames,
		dayNames:['星期日','星期一','星期二','星期三','星期四','星期五','星期六'],
		dayNamesShort:['日','一','二','三','四','五','六'],
		allDayText:'全天',
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
			date = new Date();
			var dialog=createDialog('新日程');

			$.get('/misc/gethtml/schedule/calendar_add',function(schedule_calendar_add_form){

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

					caseSelect.getOptions('cases','getListByScheduleType',type,1,function(scheduleCase){
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
						.getOptions('client','getListByCase',scheduleCase,1);
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

							$.post("/schedule/writecalendar/add",postData,
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
										showMessage('日程添加失败','notice');
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
		events: location.pathname+'/readcalendar'+location.search,
		
		eventClick: function(event) {
			$(document).showSchedule(event.id);
		},
		eventDrop: function(event,dayDelta,minuteDelta,allDay) {
			date = new Date();
			$.post("/schedule/writecalendar/drag/"+event.id,{dayDelta:dayDelta,minuteDelta:minuteDelta,allDay:Number(allDay)},function(){
				if(event.start.getTime()>date.getTime()){
					event.color='#E35B00';
				}else{
					event.color='#36C';
				}
				calendar.fullCalendar('rerenderEvents');
			});
		},
		eventResize:function(event,dayDelta,minuteDelta){
			$.post("/schedule/writecalendar/resize/"+event.id,{dayDelta:dayDelta,minuteDelta:minuteDelta,allDay:event.allDay},function(result){
				if(result!='success'){
					showMessage('日程时间数据保存失败','notice');
					console.log(result);
				}
			});
		}
	});
	
	$(window.parent).resize(function(){
		calendar.fullCalendar('option','height',$(this).height()-25);
	});
});