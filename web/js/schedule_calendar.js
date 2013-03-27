$(function(){
	
	var calendar=$('.contentTableBox#calendar:not(.fc)').fullCalendar({
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
		snapMinutes:5,
		header: {
			left: 'prev,next,today',
			center: 'title',
			right: 'agendaWeek,month,agendaDay'
		},
		selectable: true,
		selectHelper: true,
		select: function(startDate, endDate, allDay, event, view) {
			$('<div>').appendTo('body').schedule({startDate:startDate,endDate:endDate,allDay:allDay,selection:$(event.target)});
		},
		
		unselectAuto:false,

		editable: true,
		events: function(start,end,callback){
			$.get('/schedule/readcalendar/'+start.getTime()/1000+'/'+end.getTime()/1000+location.search,function(response){
				callback(response.data);
			},'json');
		},

		eventClick: function(event,jsEvent) {
			$('<div/>').appendTo(document).schedule({selection:$(jsEvent.target),event:event,method:'view'});
		},
		eventDrop: function(event,dayDelta,minuteDelta,allDay) {
			$.post('/schedule/writecalendar/drag/'+event.id,{dayDelta:dayDelta,minuteDelta:minuteDelta,allDay:Number(allDay)},function(){
				if(event.completed){
					event.color='#36C';
				}else{
					if(event.start.getTime()<new Date().getTime()){
						event.color='#555';
					}else{
						event.color='#E35B00';
					}
				}
				calendar.fullCalendar('rerenderEvents');
			});
		},
		eventResize:function(event,dayDelta,minuteDelta){
			$.post('/schedule/writecalendar/resize/'+event.id,{dayDelta:dayDelta,minuteDelta:minuteDelta,allDay:event.allDay});
		},
		droppable:true,
		drop:function(date,allDay,jsEvent){
			var data={
				time_start:date.getTime()/1000,
				time_end:date.getTime()/1000+3600,
				all_day:Number(allDay)
			};
			$.post('/schedule/writecalendar/update/'+jsEvent.target.id,data,function(response){
				event={
					start:date,
					end:new Date(date.getTime()+3600000),
					title:response.data.name,
					id:response.data.id
				};
				calendar.fullCalendar('renderEvent',event);
			});
		}
	});

	$(window).on('resize hashchange',function(){
		calendar.fullCalendar('option','height',$(window).height()-25);
	});
	
	$(document).on('resize','*',function(event){
		event.stopPropagation();
	});

});
