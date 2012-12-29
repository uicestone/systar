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
		$(calendar).createSchedule(startDate,endDate,allDay);
	},

	editable: true,
	events: '/schedule/readcalendar'+location.search,

	eventClick: function(event) {
		$(calendar).showSchedule(event.id);
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

$(window).resize(function(){
	calendar.fullCalendar('option','height',$(this).height()-25);
});