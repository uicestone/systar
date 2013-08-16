$.widget('ui.schedule',jQuery.ui.dialog,{
	options:{
		start:null,
		end:null,
		allDay:null,
		target:null,/*dialog positioning target*/
		position:{
		my:'left bottom',
			at:'right top',
			of:null
		},
		dialogClass:'shadow schedule-form',
		autoOpen:true,
		close:null,/*function on close*/
		closeText:null,
		title:'新日程',
		content:'',
		id:null,
		project:null,
		completed:null,
		in_todo_list:null,
		enrolled:null,
		buttons:[],
		method:null,/*schedule method: create, view or edit*/
		event:{},/*event object for fullCalendar*/
		calendar:null,/*日历对象，可用来判断是否从日历调取*/
		taskboard:null,/*任务墙对象，用来判断是否从任务墙调取*/
		refreshOnSave:false
	},
	_create:function(){
		var that=this;
		
		if(this.options.target){
			this.options.position.of=this.options.target;
		}
		
		this.options.close=function(){
			if(that.options.calendar){
				that.options.calendar.fullCalendar('unselect');
			}
			that._destroy();
			that.element.remove();
		};
		
		this.options.buttons=[
			{
				text: "+",
				tabIndex:-1,
				title:'添加其他选项',
				click: function(){
					var lastProfile=that.element.find('.profile:last');
					lastProfile
						.after(lastProfile.clone())
						.show()
						.children('.profile-name')
						.tagging({width:'copy'});
				}
			}
		];
		
		if(!this.options.method){
			if(this.options.id){
				this.options.method='view';
			}else{
				this.options.method='create';
			}
		}
		
		if(this.options.method==='create'){
			this.options.event={
				start:that.options.start,
				end:that.options.end,
				allDay:that.options.allDay
			};
			
			this.options.buttons.push(
				{
					text: "保存",
					tabIndex:0,
					click: function(){
						that._save();
					}
				}
			);
		}
		
		if(this.options.method==='view'){
			
			this.options.buttons.splice(this.options.buttons.length,0,{
				text:'编辑',
				tabIndex:-1,
				click:function(){
					that.element.schedule('edit');
				}
			});
		}
		
		this._super();

		if(this.options.event){
			var event=this.options.event;
			this.option('id',event.id);
			this.option('start',event.start);
			this.option('end',event.end);
			this.option('allDay',event.allDay);
		}
		
		this._getContent();
		
	},
	_getContent:function(callback){
		
		var uri,that=this;
		
		/*根据当前请求的类型判断是使用view视图还是edit视图*/
		switch(this.options.method){
			case 'view':uri='/schedule/view/'+this.options.id;break;
			case 'create':uri='/schedule/edit';break;
			case 'edit':uri='/schedule/edit/'+this.options.id;break;
		}
		
		if(this.options.project){
			uri=$.changeUrlPar(uri,'project','0');
		}
		
		if(this.options.people){
			uri=$.changeUrlPar(uri,'people',this.options.people);
		}
		
		if(!this.options.start && !this.options.end){
			uri=$.changeUrlPar(uri,'period','1');
		}
		
		uri=$.changeUrlPar(uri,'_',$.now());
		
		$.get(uri,function(response){
			
			/*根据响应，刷新日程标题*/
			if(response.data.name){
				that.option('title',response.data.name.content);
			}
			
			if(response.data.content){
				that.option('content',response.data.content.content);
			}
			
			/*根据响应，设置completed选项*/
			if(response.data.completed !==undefined && response.data.completed!==null){
				that.options.event.completed
					=that.options.completed
					=Boolean(Number(response.data.completed.content));
			}
			
			if(response.data.in_todo_list!==undefined && response.data.in_todo_list!==null){
				that.options.event.in_todo_list
					=that.options.in_todo_list
					=Boolean(Number(response.data.in_todo_list.content));
			}
			
			if(response.data.in_todo_list!==undefined && response.data.enrolled!==null){
				that.options.event.enrolled
					=that.options.enrolled
					=Boolean(Number(response.data.enrolled.content));
			}
			
			/*对于新建日程，由当前时间和日程时间给出预设的completed值*/
			if(that.options.method==='create' && that.options.completed===null){
				that.options.event.completed
					=that.options.completed
					=that.options.start<new Date() && that.options.calendar;
			}
			
			if(that.options.method==='create' && that.options.in_todo_list===null && !that.options.calendar){
				that.options.event.in_todo_list=that.options.in_todo_list=true;
			}
			
			/*根据completed选项，设置“已完成”按钮状态*/
			//TODO 争取做到根据状态直接确定按钮状态，而非事后补上
			if(that.options.completed!==null){
				that.widget().find(':checkbox[name="completed"]').prop('checked',that.options.completed);
			}
			
			if(that.options.in_todo_list!==null){
				that.widget().find(':checkbox[name="in_todo_list"]').prop('checked',that.options.in_todo_list);
			}
			
			if(that.options.enrolled!==null){
				that.widget().find(':checkbox[name="enrolled"]').prop('checked',that.options.enrolled);
			}
			
			/*触发一次按钮面板中的每个切换按钮，来使显示按钮提示文字*/
			that.widget().find('.ui-dialog-buttonpane').find(':checkbox').trigger('change',true);

			/*载入日程内容*/
			that.element.html(response.data.content.content).trigger('blockload')
			.find('[name="content"]').focus();
			
			that.widget().on('change',':input',function(){
				$(this).attr('changed','changed');
			});
			
			that.element.find('[name="project"]').tagging({width:'copy'});

			that.element.find('[name="people"]').tagging({width:'copy'});
			
			that.element.find('[name="labels"]').tagging({width:'copy'});
			
			that.element.on('focus','[name^="profiles"]',function(){
				$(this).attr('name','profiles['+$(this).prev('.profile-name').val()+']');
			});
			
			that.element.on('change','.profile-name',function(){
				$(this).next('[name^="profiles"]').attr('name','profiles['+$(this).val()+']');
			});
			
			if(that.options.project){
				that.element.find('[name="project"]').val(that.options.project).attr('changed','changed');
			}
			
			if(that.options.method==='view'){
				
				that.element.find('div.field')
					.on('mouseenter',function(){
						$(this).siblings('.profile.field[removable]').each(function(){
							if($(this).data('delete-button')){
								$(this).data('delete-button').remove();
							}
						});
					});
				
				that.element.find('.profile.field[removable]')
					.on('mouseenter',function(){
				
						var row=this;

						$(this).data('delete-button',
							$('<button/>',{text:'x'}).appendTo('body')
								.position({
									my:'right top',
									at:'right top',
									of:$(row)
								})
								.hide()
								.css({zIndex:100000})
								.on('mouseenter',function(){
									$(this).clearQueue();
								})
								.on('mouseleave',function(){
									$(this).stop().remove();
								})
								.on('click',function(){
									var button=this;
									var event_id=that.options.id;
									var profile_id=$(row).attr('id');
									$.post('/schedule/removeprofile/'+event_id+'/'+profile_id,function(){
										$(button).remove();
										that._getContent();
									});
								}).delay(100).fadeIn()
							);
					})
					.on('mouseleave','span[role]',function(){
						$(this).data('delete-button').clearQueue().delay(200).hide(0,function(){
							$(this).remove();
						});
					});
			}
			
		});
		
		if(typeof callback==='function'){
			callback();
		}
		
	},
	
	_save:function(){
		var that=this;
			
		/*根据completed按钮状态设定completed值*/
		this.options.event.completed
			=this.options.completed
			=this.element.siblings('.ui-dialog-buttonpane').find(':checkbox[name="completed"]').is(':checked');

		this.options.event.in_todo_list
			=this.options.in_todo_list
			=this.element.siblings('.ui-dialog-buttonpane').find(':checkbox[name="in_todo_list"]').is(':checked');

		this.options.event.enrolled
			=this.options.enrolled
			=this.element.siblings('.ui-dialog-buttonpane').find(':checkbox[name="enrolled"]').is(':checked');

		/*将content第一行作为name*/
		if(that.element.find(':input[name="content"]').val()){
			this.options.event.title
				=this.options.title
				=this.element.find(':input[name="content"]').val().split("\n").shift();
		}
		else if(that.options.method!=='view'){
			$.showMessage('请填写内容','warning');
			return;
		}

		/*初始化准备提交的数据*/
		var data={
			completed:Number(this.options.completed),
			in_todo_list:Number(this.options.in_todo_list),
			enrolled:Number(this.options.enrolled),
			name:this.options.title
		};

		/*对于新建日程，添加日历时间数据*/
		if(this.options.method==='create' && this.options.start !== null && this.options.end !== null && this.options.allDay !== null){
			$.extend(data,{
				start:this.options.start.getTime()/1000,
				end:this.options.end.getTime()/1000,
				all_day:Number(this.options.allDay)
			});
		}
		
		if(this.options.project){
			data.project=this.options.project;
		}

		that.element.find(':input[name][changed]').each(function(){
			if($(this).is(':checkbox')){
				data[$(this).attr('name')]=Number($(this).is(':checked'));
			}else{
				data[$(this).attr('name')]=$(this).val();
			}
		});
		
		var uri=this.options.method==='create'?'/schedule/writecalendar/add':'/schedule/writecalendar/update/'+this.options.id;

		$.post(uri,data,function(response){

			if(response.status==='success'){
				
				if(that.options.calendar){
					that.options.id=that.options.event.id=response.data.id;

					if(that.options.completed){
						that.options.event.color='#36C';
					}else{
						if(that.options.start && that.options.start.getTime()<new Date().getTime()){
							that.options.event.color='#555';
						}else{
							that.options.event.color='#E35B00';
						}
					}
					$(that.options.calendar)
					.fullCalendar(that.options.method==='create'?'renderEvent':'updateEvent',that.options.event);
				}
				
				/*并非从日历中点击打开的日程，保存时我们将刷新日历*/
				else if(typeof calendar!=='undefined' && $(calendar)){
					$(calendar).fullCalendar('refetchEvents');
				}
				
				if(that.options.refreshOnSave){
					$.refresh(hash);
				}
				
				/*对于所有日程 ，保存时我们刷新任务列表*/
				$.get('schedule/todolist');
				
				that.element.schedule('close');
			}
		});
	},
	
	_createButtons:function(buttons){

		var that=this;

		this._super(buttons);
		
		var ui_id;
		
		if(this.options.id){
			ui_id=this.options.id;
		}else{
			ui_id=this.element.attr('id');
		}
		
		var buttonCheckbox=this.widget().children('.ui-dialog-buttonpane').find('#completed-'+ui_id);
		
		if(!buttonCheckbox.length){
			var buttonCheckbox=$('<div class="ui-dialog-buttonset" style="float:left;padding:.5em .4em"><input type="checkbox" id="completed-'+ui_id+'" name="completed" title-checked="已完成" title-unchecked="未完成" /><label for="completed-'+ui_id+'"><span class="icon-checkmark"></span></label></div>')
			.appendTo(this.widget().children('.ui-dialog-buttonpane'))
			.find('#completed-'+ui_id)
				.on('change',function(event,isOnloadTrigger){
					!isOnloadTrigger && that.options.id && $.post('/schedule/setcompleted/'+that.options.id+'/'+Number($(this).is(':checked')));
				});
		}
		buttonCheckbox.button();
		
		var in_todo_list=this.widget().children('.ui-dialog-buttonpane').find('#in-todo-list-'+ui_id);
		
		if(!in_todo_list.length){
			var in_todo_list=$('<div class="ui-dialog-buttonset" style="float:left;padding:.5em .4em"><input type="checkbox" id="in-todo-list-'+ui_id+'" name="in_todo_list" title-checked="在任务列表中显示" title-unchecked="不在任务列表中显示" /><label for="in-todo-list-'+ui_id+'"><span class="icon-list"></span></label></div>')
			.appendTo(this.widget().children('.ui-dialog-buttonpane'))
			.find('#in-todo-list-'+ui_id)
				.on('change',function(event,isOnloadTrigger){
					!isOnloadTrigger && that.options.id && $.post('/schedule/showintodolist/'+that.options.id+'/'+Number($(this).is(':checked')));
				});
		}
		in_todo_list.button();
		
		var enrolled=this.widget().children('.ui-dialog-buttonpane').find('#enrolled-'+ui_id);
		
		if(!enrolled.length){
			var enrolled=$('<div class="ui-dialog-buttonset" style="float:left;padding:.5em .4em"><input type="checkbox" id="enrolled-'+ui_id+'" name="enrolled" title-checked="计入我的工作时间" title-unchecked="不计入我的工作时间" checked="checked" /><label for="enrolled-'+ui_id+'"><span class="icon-clock"></span></label></div>')
			.appendTo(this.widget().children('.ui-dialog-buttonpane'))
			.find('#enrolled-'+ui_id)
				.on('change',function(event,isOnloadTrigger){
					!isOnloadTrigger && that.options.id && $.post('/schedule/enroll/'+that.options.id+'/'+Number($(this).is(':checked')));
				});
		}
		enrolled.button();
	},
	
	edit:function(){
		var that=this;
		this.option('method','edit');
		this._getContent(function(){
			/*对于编辑页面，删除编辑按钮，并换成一个删除按钮*/
			that.options.buttons.pop();
			that.options.buttons.push(
				{
					text:'删除',
					tabIndex:-1,
					click:function(){
						$.get('/schedule/delete/'+that.options.id,function(response){
							if(response.status==='success'){
								that.element.schedule('close');
								$(calendar).fullCalendar('removeEvents',that.options.id);
							}
						});
					}
				}
			);
			that.options.buttons.push(
				{
					text: "保存",
					tabIndex:0,
					click: function(){
						that._save();
					}
				}
			);
			that.option('buttons',that.options.buttons);
		});
	}
});


jQuery.each(['createSchedule','viewSchedule'],function(i,method){
	jQuery[method]=function(args){
		if(method==='viewSchedule'){
			$.extend(args,{method:'view'});
		}
		$('<div/>').appendTo('body').schedule(args);
	}
});
