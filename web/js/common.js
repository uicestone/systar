/*跳转IE6用户*/
if($.browser.msie && $.browser.version<7 && !(controller=='user' && action=='browser')){
	/*跳转到浏览器推荐页面*/
	window.location.hash='user/browser';
	/*停止载入页面*/
	exit();
}

$(window).hashchange(function(){

	$.get(window.location.hash.substr(1),function(response){
		$(document).setBlock(response);
	},'json');

});

$(document).ready(function(){

	/*为主体载入指定页面或默认页面*/
	if(window.location.hash){
		$(window).trigger('hashchange');
	}else{
		window.location.hash=$('#page').attr('default-uri');
	}
})
/*主体页面加载事件*/
.on('pageLoaded','#page',function(){
	$('[placeholder]').placeholder()
	$('.date').datepicker();
	
	$('.birthday').datepicker({
		changeMonth: true,
		changeYear: true,
		defaultDate:'1997-1-1'
	});
	
	//$('[display-for]:input:not([locked-by]),[display-for]:not([locked-by]) :input:not([locked-by])').attr('disabled','disabled');
	$('[display-for]:not([locked-by])').hide();

	$('title').html(affair+' - '+(username?username+' - ':'')+sysname);

	//设置导航菜单高亮
	$('#navMenu li#nav-'+controller).addClass('activated').siblings('li').removeClass('activated');
	$('#navMenu ul.l1 li').removeClass('activated').parent().parent().parent().find('li#nav-'+controller+'-'+action).addClass('activated');

	if(!$.browser.msie){
		$('.contentTable:not(.search-bar)').children('tbody').children('tr').each(function(index){
			$(this).delay(15*index).css('opacity',0).css('visibility','visible').animate({opacity:'1'},500);
		});
	}
})
/*编辑页的提交按钮点击事件，提交数据到后台，在页面上反馈数据和提示*/
.on('click','#page>form input:submit',function(){
	var id = $('form[name="'+controller+'"]').attr('id');
	var submit = $(this).attr('name').replace('submit[','').replace(']','');
	
	var postURI='/'+controller+'/submit/'+submit;
	
	if(id){
		postURI+='/'+id;
	}

	$.post(postURI,$('#page>form').serialize(),function(response){
		$(document).setBlock(response);
	},'json');
	return false;
})
/*edit表单元素更改时实时提交到后台 */
.on('change','#page>form:[id] :input',function(){
	var value=$(this).val();
	if($(this).is(':checkbox') && !$(this).is(':checked')){
		value=0;
	}
	var id = $('#page>form').attr('id');
	var name = $(this).attr('name').replace('[','/').replace(']','');
	var data={};data[name]=value;
	$.post('/'+controller+'/setfields/'+id,data);
})
/*截获所有链接点击事件，用以加载主体页面（弹窗页面除外）*/
.on('click','a:not([href^="javascript"]):not([href^="#"])',function(){
	var href=$(this).attr('href');
	$('#page').load($(this).attr('href'),function(){
		$(this).trigger('pageLoaded');

		//设置顶层框架的hash为当前框架的URI
		window.location.hash='#'+href.replace(RegExp('^/'),'');
	});

	return false;
})
/*边栏最小化*/
.on('click','.minimize-button',function(){
	$('#toolBar').toggleClass('minimized');
	var minimized=0;
	if($('#toolBar').hasClass('minimized')){
		minimized=1;
	}
	$.get('/misc/setsession/minimized',function(result){
		if(result!='success'){
			showMessage('与服务器通信失败','warning');
			console.log(result);
		}
	});
})
/*边栏选框自动提交*/
.on('change','select.filter[method!="get"]',function(){
	post($(this).attr('name'),$(this).val());
})
/*边栏选框自动提交*/
.on('change','select.filter[method="get"]',function(){
	redirectPara($(this));
})
/*自动完成*/
.on('focus','[autocomplete-model]',function(){
	var autocompleteModel=$(this).attr('autocomplete-model');
	$(this).autocomplete({
		source: function(request, response){
			$.post('/'+autocompleteModel+'/match',{term:request.term},function(responseJSON){
				response(responseJSON.data);
			},'json');
		},
		select: function(event,ui){
			$(this).trigger('autocompleteselect',{value:ui.item.value}).change();
			return false;
		},
		focus: function(event,ui){
			$(this).val(ui.item.label);
			return false;
		},
		response: function(event,ui){
			if(ui.content.length==0){
				$(this).trigger('autocompletenoresult');
			}
			$(this).change();
		}
	})
	/*.bind('input.autocomplete', function(){
		//修正firefox下中文不自动search的bug
		$(this).trigger('keydown.autocomplete'); 
	})*/
	//.autocomplete('search')
	;
})
.on('click','.item>.title>.toggle-add-form',function(){
	var addForm=$(this).parent().siblings('.add-form');
	if(addForm.is(':hidden')){
		addForm.show(200).find('select:visible').change();
		$(this).html('-');
	}else{
		addForm.hide(200);
		$(this).html('+');
	}
})
.on('enable','[display-for]:not([locked-by])',function(){
	//console.log('enabled:');
	//console.log(this);
	$(this).show();
	/*if($(this).is(':input')){
		$(this).removeAttr('disabled');
	}else{
		$(this).find(':input').removeAttr('disabled');
	}*/
	//return false;
})
.on('disable','[display-for]:not([locked-by])',function(){
	//console.log('disabled:');
	//console.log(this);
	$(this).hide();
	/*if($(this).is(':input')){
		$(this).attr('disabled','disabled');
	}else{
		$(this).find(':input').attr('disabled','disabled');
	}*/
	//return false;
}).on('mouseenter mouseleave','.contentTable>tbody>tr',function(){
	$(this).toggleClass('highlighted');

}).on('click','.contentTable>tbody>tr:has(td[href])',function(){
	window.location.hash=$(this).children('td:first').attr('href');

}).on('click','.contentTable>tbody>tr a',function(){
	event.stopPropagation();

});

function exit(){
	if (window.stop){
		window.stop();
	}else{
		document.execCommand("Stop");
	}
}

function isArray(o) {
	//判断对象是否是数组
	return Object.prototype.toString.call(o) === '[object Array]';
}

function changeURLPar(url,par,par_value){
	//为url添加/更改变量名和值，并返回

	var pattern = '[^&^?]*'+par+'=[^&]*';
	var replaceText = par+'='+par_value;
	
	if (url.match(pattern)){
		return url.replace(url.match(pattern), replaceText);
	}else{
		if (url.match('[\?]')){
			return url+'&'+ replaceText;
		}else{
			return url+'?'+replaceText;
		}
	}

	return url+'\n'+par+'\n'+par_value;
}

function unsetURLPar(url,par){
	//删除url中的指定变量，并返回
	var regUnsetPara=new RegExp('\\?'+par+'$|\\?'+par+'=[^&]*$|'+par+'=[^&]*\\&*|'+par+'&|'+par+'$');
	return url.replace(regUnsetPara,'');
}

function redirectPara(obj,unsetPara,specifiedName,specifiedValue){
	//根据当前对象的name和value跳转url
	var url=location.href;
	var name='option';
	var value='';

	if(specifiedName){
		name=specifiedName;
	}else if($(obj).attr('name')){
		name=$(obj).attr('name');
	}

	if(specifiedValue){
		value=specifiedValue;
	}else if($(obj).val()){
		value=$(obj).val();
	}

	if(unsetPara){
		url=unsetURLPar(url,unsetPara);
	}

	if(value==''){
		url=unsetURLPar(url,name);
	}else{
		url=changeURLPar(url,name,value);
	}

	location.href=url;
}

function post(name,value){
	//直接post一个变量并刷新页
	var jsPostForm=document.createElement("form"); 
	jsPostForm.method="post";
	jsPostForm.name="jsPostForm";
	
	var jsPostInput=document.createElement("input") ; 
	jsPostInput.setAttribute("name", name) ; 
	jsPostInput.setAttribute("value", value); 
	jsPostForm.appendChild(jsPostInput) ;

	document.body.appendChild(jsPostForm) ; 
	jsPostForm.submit() ; 
	document.body.removeChild(jsPostForm) ;
}

function postArr(arr){
	//直接post一组变量并刷新页面
	var jsPostForm = document.createElement("form"); 
	jsPostForm.method="post" ; 
	
	for(var key in arr){
		var jsPostInput = document.createElement("input") ; 
		jsPostInput.setAttribute("name",key) ; 
		jsPostInput.setAttribute("value",arr[key]); 
		jsPostForm.appendChild(jsPostInput) ;
	}

	document.body.appendChild(jsPostForm) ; 
	jsPostForm.submit() ; 
	document.body.removeChild(jsPostForm) ;
}

function postOrderby(orderby){
/*
	在contentTable的列中，post根据特定表的本参数排序。
	如postOrderby('status','client_catologsale'),
	由后台processOrderby处理后将生成ORDER BY client_catologsale.status的语句
*/
	var arr=new Array();
	arr.orderby=orderby;
	postArr(arr);
}

function processMessage(){
	//将本页的多条position:absulote的message分开显示
	var notices=$('.message').size();

	var noticeEdge=50;
	var lastNoticeHeight=0;
	$('.message').each(function(index,element){
		$(this).css('top',noticeEdge+lastNoticeHeight+'px');
		lastNoticeHeight+=$(this).height()+30;
	});

	$('.message').click(function(){
		$(this).stop(true).fadeOut(200,function(){
			$(this).remove();
			processMessage();
		});
	}).each(function(index,Element){
		$(this).delay(index*3000).fadeOut(20000,function(){
			$(this).remove();
		});
	}).mouseenter(function(){
		$(this).stop(true).dequeue().css('opacity',1);
	}).mouseout(function(){
		$(this).fadeOut(10000);
	});
}

function showMessage(message,type,directExport){
	//js方式输出提示条，输出前先删除之前的提示
	if(!directExport){
		var directExport=false;
	}

	$('.message').hide().remove();
	
	if(directExport){
		var newMessage=$(message);
	}else{
		if(type=='warning'){
			var notice_class='ui-state-error';
			var notice_symbol='<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>';
		}else{
			var notice_class='ui-state-highlight';
			var notice_symbol='<span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>';
		}
		var newMessage = $('<span class="message ui-corner-all ' + notice_class + '" title="点击隐藏提示">' + notice_symbol + message + '</span>');
	}

	newMessage.appendTo('body');

	processMessage();
}

function showWindow(targetUrl,width,height){
	var date=new Date();
	if(!width){
		var width=920;
	}
	
	if(!height){
		var height=screen.height-300;
	}
	window.open('/'+targetUrl,date.getTime(),'height='+height+',width='+width+', top=100,left=100, toolbar=no, menubar=no, scrollbars=yes, resizable=yes,location=no, status=no, titlebar=no');
}

function keyPressHandler(button,waitKeyCode){
	if(!waitKeyCode){
		var waitKeyCode=13;
	}
	
	if(event.keyCode == waitKeyCode){ 
		event.returnValue=false; 
		event.cancel = true; 
		button.click(); 
	}
}
	
jQuery.fn.createDialog=function(title,html){
	//$('#dialog').remove();//创建一个对话框的时候，先删除其他对话框
	var dialogs=$('.dialog').length;
	var dialog=$('<div id="dialog_'+(dialogs+1)+'" class="dialog"></div>').insertAfter($(this))
	.dialog({
		title:title,
		position:['middle', 200],minHeight:300,minWidth:500,
		autoOpen:false,show:'fade',hide:'fade',
		close:function(){$(this).remove()}
	});
	
	if(html){
		dialog.html(html);
	}
	
	return dialog;
}

jQuery.fn.showSchedule=function(eventId){
	var calendar=$(this);
	$.get("/schedule/view/"+eventId,function(schedule){
		
		var dialog=$(this).createDialog(schedule.name,schedule.view)
		.dialog( "option", "buttons", [
			{
				text: "编辑",
				click: function(){
					$(this).editSchedule(eventId,calendar);
				}
			}
		]);

		if($(calendar).attr('id')=='calendar'){
			dialog.dialog('option','buttons',[{
				text:'添加至任务墙',
				click:function(){
					$.get('/schedule/addtotaskboard/'+eventId,function(){
						dialog.dialog('close');
					});
				}
			}].concat(dialog.dialog('option','buttons')));
		}else if($(calendar).attr('id')=='taskboard'){
			dialog.dialog('option','buttons',[{
				text:'移出任务墙',
				click:function(){
					$.get('/schedule/deletefromtaskboard/'+eventId,function(){
						dialog.dialog('close');
						$("#task_"+eventId).remove();
					});
					
				}
			}].concat(dialog.dialog('option','buttons')));
		}
		
		dialog.dialog('open');
	},'json');
}

jQuery.fn.createSchedule=function(startDate, endDate, allDay){
	date = new Date();
	calendar=$(this);
	
	var dialog;
	if(startDate && endDate){
		dialog=$(this).createDialog('新建日程');
	}
	else{
		dialog=$(this).createDialog('新建任务')
	}
	$.get('/schedule/ajaxedit',function(schedule_calendar_add_form){

		dialog.html(schedule_calendar_add_form)
			.find('#combobox')
			.combobox();
	
		/*配置type和completed默认值*/
		$('input[name="type"][value="0"]').attr('checked','checked');
		if(startDate){
			$('input[name="completed"][value="'+(startDate.getTime()<date.getTime()?1:0)+'"]').attr('checked','checked');
		}
		var typeRadio = $('input[name="type"]');
		var caseSelect = $('select[name="case"]');

		/*监听项目类别变化*/
		typeRadio.change(function(){
			var type=$('input[name="type"]:checked').val();

			caseSelect.getOptions('cases','getListByScheduleType',type,1,function(scheduleCase){
				caseSelect.trigger('change',{scheduleCase:scheduleCase,type:type});
			});
		});

		/*监听案件变化*/
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

	dialog.dialog( "option", "buttons", [{
		text: "保存",
		click: function() {
			if($('input[name="name"]').val()==''){
				if(startDate){
					alert('日志名称必填');
				}
				else alert('任务名称必填');
				$('input:[name="name"]').focus();
			}else{
				if(startDate && endDate){
					var start=startDate.getTime()/1000;
					var end=endDate.getTime()/1000;
					var postData=$.extend($('#schedule').serializeJSON(),{time_start:start,time_end:end,all_day:Number(allDay)});
					delete postData.type;
					$.post("/schedule/writecalendar/add",postData,
						function(response){
							if(isNaN(response) && response !=null){
								calendar.fullCalendar('renderEvent',
									{
										id:response['id'],
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
								console.log(response);
							}
						}
					);
				}else{
						var postData=$('#schedule').serializeJSON();
						delete postData.type;
						$.post("/schedule/writecalendar/add",postData,
							function(response){
								$('.column:first').append('<div class="portlet" id="task_'+response['id']+'">'+
																'<div class="portlet-header">'+response['name']+'</div>'+
																'<div class="portlet-content">'+response['content']+'</div>'+
																'</div>')
								$(".portlet" ).click(function(){
										$('#taskboard').showSchedule($(this).attr('id').replace('task_',''));
								})
								$('.column:first')
								.find( ".portlet:last" ).
									addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
					            .find( ".portlet-header" )
					                .addClass( "ui-widget-header ui-corner-all" )
					                .prepend( "<span class='ui-icon ui-icon-minusthick'></span>")
					                .end()
					            .find( ".portlet-content" );
								
								$( ".portlet-header .ui-icon" ).click(function(){
						            $( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
						            $( this ).parents( ".portlet:first" ).find( ".portlet-content" ).toggle();
						        });
								
								dialog.dialog('close');
							});
						
					}
				}
		}
	}])
	.dialog('open');
}

jQuery.fn.editSchedule=function(eventId,calendar){
	dialog=$(this);
	
	$.get('/schedule/ajaxedit/'+eventId,function(html){
		dialog.html(html);
		/*
		$('[name="name"]').val(schedule.name);
		$('[name="content"]').val(schedule.content);
		$('[name="experience"]').val(schedule.experience);
		$('[name="place"]').val(schedule.place);
		$('[name="fee"]').val(schedule.fee);
		$('[name="fee_name"]').val(schedule.fee_name);
		//$('[name="name"]').val(schedule.name);
		$('[name="completed"][value="'+schedule.completed+'"]').attr('checked','checked');
		*/

		dialog.dialog( "option", "buttons", [
			{
				text: "删除",
				click: function() {
					$.get("/schedule/writecalendar/delete/"+eventId,function(result){
						console.log(result);
					});
					$(this).dialog("close");
					if($(calendar).attr('id')=='calendar')
					calendar.fullCalendar('removeEvents',[eventId]);
					else if($(calendar).attr('id')=='taskboard'){
						$("#task_"+eventId).remove();
					}
				}
			},
			{
				text: "高级",
				click: function() {
					$(this).dialog("close");
					showWindow('schedule/edit/'+eventId);
				}
			},
			{
				text: "保存",
				click: function() {
					$.post("/schedule/writecalendar/update/"+eventId,{
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

jQuery.fn.getOptions=function(affair,method,active_value,select_type,callback){
	/*
	 * select_type 是选框的数据类型，可选0(type)和1(data)两个值，默认为0(type)
	 */
	if(!select_type){
		select_type=0;
	}
	var passive_select=$(this);
	$.post('/misc/getselectoption',{affair:affair,method:method,active_value:active_value,select_type:select_type},function(options_html){
		passive_select.html('')
		.html(options_html);
		if (typeof callback != 'undefined'){
			callback(passive_select.val());
		}
	});
	return $(this);
}

jQuery.fn.getOptionsByLabelRelative=function(labelName,callback){
	var select=$(this);
	
	$.get('/label/getrelatives/'+labelName,function(response){
		var options='';
		$.map(response.data,function(item,index){
			options+='<option value="'+index+'">'+item+'</option>';
		})
		select.html(options).change();
		if (typeof callback != 'undefined'){
			callback(passive_select.val());
		}
	},'json');
}

jQuery.fn.addRow=function(rowData){
	if(!$(this).is('.contentTable')){
		console.error('addRow方法调用错误，只有.contenTable才能addRow');
		return false;
	}
	
	var fields=[];
	
	$(this).find('thead>tr>th').each(function(){
		fields.push($(this).attr('field'));
	});
	
	var newRow='';
	
	$.map(fields,function(field){
		newRow+='<td field="'+field+'">'+rowData[field]+'</td>';
	});
	
	newRow=$('<tr style="opacity: 1; visibility: visible;">'+newRow+'</tr>');
	
	var currentRows=$(this).find('tbody>tr').length;
	
	if(currentRows%2==1){
		newRow.addClass('oddLine');
	}
	
	$(this).find('tbody').append(newRow);
	
	return true;
}

jQuery.fn.serializeJSON=function() {
	var json = {};
	jQuery.map($(this).serializeArray(), function(n, i){
		json[n['name']] = n['value'];
	});
	return json;
};

jQuery.fn.reset=function(){
	$(this).find(':input:not(:submit):not(select)').val('');
	$(this).find(':input[default-value]').val($(this).attr('default-value'));
}

jQuery.parseResponse=function(response){
	try{
		var parseResponse=$.parseJSON(response);
		return parseResponse;
	}catch(e){
		showMessage(response,'warning');
		return false;
	}
}

jQuery.fn.setBlock=function(response){
	
	function set(data){
		switch(data.type){
			case 'uri':
				if(data.content==null){
					data.content=$(data.selector).attr('default-uri');
				}
				if(data.selector=='#page'){
					window.location.hash='#'+data.content;
				}else{
					$.get(data.content,function(response){
						if(data.method=='replace'){
							$(data.selector).replaceWith(response.data);
							$(data.selector).trigger('blockLoaded');
						}else{
							$(data.selector).html(response.data).trigger('blockLoaded');
						}
					},'json');
				}
				break;
			case 'html':
				if(data.method=='replace'){
					$(data.selector).replaceWith(data.content);
				}else{
					$(data.selector).html(data.content);
				}

				if(data.selector=='#page'){
					$('#page').trigger('pageLoaded');
				}else{
					$(data.selector).trigger('blockLoaded');
				}

				break;
		}
	}
	
	if(response.status=='login_required'){
		response.data=[{'type':'uri','content':'nav','selector':'nav'},{'type':'uri','content':'login','selector':'#page'}];
	}

	if(response.message){
		$.each(response.message,function(messageType,messages){
			$.each(messages,function(index,message){
				showMessage(message,messageType);
			});
		});
	}

	if(response.data.type){
		/*data不是数组*/
		set(response.data);
	}else{
		/*data是数组，先遍历*/
		$.each(response.data,function(index,data){
			set(data);
		});
	}
}

var currentWindow=window;
while(typeof currentWindow.opener !=='undefined' && currentWindow.opener!==null){
	currentWindow=currentWindow.opener;
}
window.rootOpener=currentWindow;