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
		title:'新日程',
		content:'',
		id:null,
		project:null,
		buttons:[],
		method:null,/*schedule method: create, view or edit*/
		event:{},/*event object for fullCalendar*/
		calendar:null,/*日历对象，可用来判断是否从日历调取*/
		taskboard:null/*任务墙对象，用来判断是否从任务墙调取*/
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
						.select2({
							dropdownCss:{minWidth:100},
							createSearchChoice:function(term,results){
								if(typeof results==='undefined'){
									return {id:term,text:term,create:true};
								}

								var options=[];
								$.each(results,function(){
									options.push(this.text);
								});

								if($.inArray(term,options)===-1){
									return {id:term,text:term,create:true};
								}
							},
							formatSelection:function(object,container){
								if(this.element.find('option[value="'+object.id+'"]').length===0){
									this.element.append($('<option/>',{value:object.id,text:object.text}));

									if(this.element.is('[multiple]')){
										var val=this.element.val();
										if(!val){
											val=[];
										}
										val.push(object.id);
										this.element.val(val);
									}
									else{
										this.element.val(object.id);
									}

								}
								return object.text;
							},
							formatResult:function(object,container,query){
								if(object.create){
									return '添加：'+object.text;
								}
								else if(object.text){
									return object.text;
								}
								else{
									return object.id;
								}
							}
						});
				}
			},

			{
				text: "保存",
				tabIndex:0,
				click: function(){
					that._save();
				}
			}
		];
		
		if(this.options.calendar || this.options.taskboard){
			this.options.buttons.unshift({
				text:'>',
				tabIndex:-1,
				title:'添加到任务列表',
				click:function(){
					$.post('/schedule/writecalendar/update/'+that.options.id,{in_todo_list:1},function(){
						$.refresh(hash);
					});
					if(that.options.taskboard){
						that.element.schedule('close');
						$.get('/schedule/removefromtaskboard/'+that.options.id);
					}
				}
			});
		}
		
		if(!this.options.method){
			if(this.options.id || this.event){
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
		}
		
		if(this.options.method==='view'){
			
			this.options.buttons.splice(this.options.buttons.length-1,0,{
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
	
	_getContent:function(){
		
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
		
		if(!this.options.start && !this.options.end){
			uri=$.changeUrlPar(uri,'period','1');
		}
		
		$.get(uri,function(response){
			
			/*根据响应，刷新日程标题*/
			if(response.data.name){
				that.option('title',response.data.name.content);
			}
			
			if(response.data.content){
				that.option('content',response.data.content.content);
			}
			
			/*根据响应，设置completed选项*/
			response.data.completed && that.option('completed',Boolean(Number(response.data.completed.content)));
			
			/*对于新建日程，由当前时间和日程时间给出预设的completed值*/
			if(that.options.method==='create'){
				that.options.completed=that.options.start<new Date() && that.options.calendar;
			}
			
			/*根据completed选项，设置“已完成”按钮状态*/
			if(that.options.completed){
				that.widget().find(':checkbox#completed').attr('checked','checked');
			}
			
			/*触发一次“已完成”按钮，来使显示按钮文字*/
			that.widget().find(':checkbox#completed').trigger('change');

			/*载入日程内容*/
			that.element.html(response.data.content.content).trigger('blockload')
			.find('[name="content"]').focus();
			
			that.widget().on('change',':input',function(){
				$(this).attr('changed','changed');
			});
			
			that.element.find('[name="project"]').select2();
			
			that.element.find('[name="people"]').select2();
			
			that.element.on('focus','[name^="profiles"]',function(){
				$(this).attr('name','profiles['+$(this).prev('.profile-name').val()+']');
			});
			
			that.element.on('change','.profile-name',function(){
				$(this).next('[name^="profiles"]').attr('name','profiles['+$(this).val()+']');
			});
			
			if(that.options.project){
				that.element.find('[name="project"]').val(that.options.project).trigger('liszt:updated').attr('changed','changed');
			}
			
		},'json');
	},
	
	_save:function(){
		var that=this;
			
		/*根据completed按钮状态设定completed值*/
		this.options.event.completed
			=this.options.completed
			=this.element.siblings('.ui-dialog-buttonpane').find(':checkbox[name="completed"]').is(':checked');

		/*将content第一行作为name*/
		if(that.element.find(':input[name="content"]').val()){
			this.options.event.title
				=this.options.title
				=this.element.find(':input[name="content"]').val().split("\n").shift();
		}else{
			$.showMessage('请填写内容','warning');
			return;
		}

		/*初始化准备提交的数据*/
		var data={
			completed:Number(this.options.completed),
			name:this.options.title
		};

		/*对于新建日程，添加日历时间数据*/
		if(this.options.method==='create' && this.options.start !== null && this.options.end !== null && this.options.allDay !== null){
			$.extend(data,{
				time_start:this.options.start.getTime()/1000,
				time_end:this.options.end.getTime()/1000,
				all_day:Number(this.options.allDay)
			});
		}

		that.element.find(':input[name][changed]').each(function(){
			data[$(this).attr('name')]=$(this).val();
		});

		var uri=this.options.method==='create'?'/schedule/writecalendar/add':'/schedule/writecalendar/update/'+this.options.id;

		$.post(uri,data,function(response){

			if(response.status==='success'){
				
				if(typeof calendar!=='undefined' && $(calendar)){
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
					$(calendar)
					.fullCalendar(that.options.method==='create'?'renderEvent':'updateEvent',that.options.event);
				}
				that.element.schedule('close');
			}
		});
	},
	
	_createButtons:function(buttons){
		this._super(buttons);
		
		var buttonCheckbox=this.widget().children('.ui-dialog-buttonpane').find('#completed');
		
		if(!buttonCheckbox.length){
			var buttonCheckbox=$('<div class="ui-dialog-buttonset" style="float:left;padding:.5em .4em"><input type="checkbox" id="completed" name="completed" text-checked="已完成" text-unchecked="未完成" /><label for="completed" ></label></div>')
			.appendTo(this.widget().children('.ui-dialog-buttonpane'))
			.find('#completed');
		}
		buttonCheckbox.button();
	},
	
	edit:function(){
		that=this;
		this.option('method','edit');
		this._getContent();
		
		/*对于编辑页面，删除编辑按钮，并换成一个删除按钮*/
		this.options.buttons.splice(1,1,{
			text:'删除',
			tabIndex:-1,
			click:function(){
				$.get('/schedule/writecalendar/delete/'+that.options.id,function(response){
					if(response.status==='success'){
						that.element.schedule('close');
						$(calendar).fullCalendar('removeEvents',that.options.id);
					}
				},'json');
			}
		});
		this.option('buttons',this.options.buttons);
		
		return this.element;
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
