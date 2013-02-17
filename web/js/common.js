/*跳转IE6用户*/
/*if($.browser.msie && $.browser.version<7 && !(controller=='user' && action=='browser')){
	$.locationHash('user/browser');
}*/

var hash,controller,action,username,sysname,uriSegments;

$(window).on('hashchange',function(){console.log('hashchange fired: '+window.location.hash);

	hash=$.locationHash();
	uriSegments=hash.split('/');
	
	/*根据当前hash，设置标签选项卡激活状态*/
	$('#tabs>[for="'+hash+'"]').addClass('activated');
	$('#tabs>:not([for="'+hash+'"])').removeClass('activated');

	/*
	 *根据当前hash，显示对应标签页面，隐藏其他页面。
	 *如果当前page中没有请求的页面（或者已过期），那么向服务器发送请求，获取新的页面并添加标签选项卡。
	 */
	$('#page>section[hash!="'+hash+'"]').hide();
	$('#side-bar>aside[for!="'+hash+'"]').hide();
	
	if($('#page>section[hash="'+hash+'"]').length>0){
		$('#page>section[hash="'+hash+'"]').show().attr('time-access',$.now()).trigger('sectionshow');
		$('#side-bar>aside[for="'+hash+'"]').show().trigger('sidebarshow');
		
	}else{
		$.get(hash,function(response){
			
			var page=$('<section hash="'+hash+'"></section>').appendTo('#page');
			var sidebar=$('<aside for="'+hash+'"></aside>').appendTo('#side-bar');
			
			if(response.status=='login_required'){
				window.location.href='login';
				return this;
			}

			else if(response.status=='redirect'){
				$.locationHash(response.data);
				return this;
			}

			else if(response.status=='refresh'){
				$.get(hash,function(response){
					$(document).setBlock(response);
				});
			}

			$.parseMessage(response.message);
	
			/*如果请求的hash在导航菜单中存在，则不生成标签选项卡*/
			if($('nav a[href="#'+hash+'"]').length==0){
				$('#tabs').append('<li for="'+hash+'" class="activated"><a href="#'+hash+'">'+response.data.name.content+'</a></li>');
			}
			
			page.attr('time-load',$.now()).attr('time-access',$.now()).html(response.data.content.content).trigger('sectionload');
			
			if(response.data.sidebar){
				sidebar.html(response.data.sidebar.content).trigger('sidebarload');
			}
			
		},'json');
	}
	
	$('nav li').removeClass('activated');
	$('nav li#nav-'+uriSegments[0]+', nav li#nav-'+uriSegments[0]+'-'+uriSegments[1]).addClass('activated');
});

$(document).ready(function(){
	/*导航栏配置*/
	$('#navMenu>.l0>li>a,controller').click(function(){
		$(this).parent().children('ul:hidden').show();
		$(this).siblings('.arrow').children('img').rotate({animateTo:90,duration:200});
	});
	$('#navMenu>.l0>li>.arrow').click(function(){
		var subMenu=$(this).siblings('.l1');
		if(subMenu.is(':hidden')){
			subMenu.show(200);
			$(this).children('img').rotate({animateTo:90,duration:200});
		}else{
			$(this).children('img').rotate({animateTo:0,duration:200});
			subMenu.hide(200);
		}
	});
	
	/*为主体载入指定页面或默认页面*/
	if(window.location.hash){
		$(window).trigger('hashchange');
	}else if($('#page').attr('default-uri')){
 		$.locationHash($('#page').attr('default-uri'));
 	}
	
	$('body').trigger('sectionload');
})
/*手动刷新*/
.on('click','a[href^="#"]',function(){
	if($(this).attr('href').substr(1)==hash){
		$.get(hash,function(response){
			$(document).setBlock(response);
		},'json');
	}
})
/*主体页面加载事件*/
.on('sectionload blockload','#page>section,body',function(event){
	/*section触发事件后不再传递到body*/
	event.stopPropagation();
	
	$(this).find('[placeholder]').placeholder()
	$(this).find('.date').datepicker();
	$(this).find('.birthday').datepicker({
		changeMonth: true,
		changeYear: true
	});
	
	$(this).find('.contentTable>tbody>tr:has(td:first[hash])').css({cursor:'pointer'});
	
	if(!$.browser.msie){
		$(this).find('.contentTable:not(#side-bar .contentTable)>tbody>tr').each(function(index){
			$(this).delay(15*index).css('opacity',0).css('visibility','visible').animate({opacity:'1'},500);
		});
	}
})
.on('blockload','.contentTable, #page>section',function(event){
	event.stopPropagation();
	
	if(!$.browser.msie){
		$(this).find('tbody>tr').each(function(index){
			$(this).delay(15*index).css('opacity',0).css('visibility','visible').animate({opacity:'1'},500);
		});
	}
})
.on('sectionload sectionshow','#page>section',function(){
	document.title=affair+' - '+(username?username+' - ':'')+sysname;
})
/*编辑页的提交按钮点击事件，提交数据到后台，在页面上反馈数据和提示*/
.on('click','#page>section>form input:submit, #page>section>form button:submit',function(){
	var id = $('form[name="'+controller+'"]').attr('id');
	var submit = $(this).attr('name').replace('submit[','').replace(']','');
	
	var postURI='/'+controller+'/submit/'+submit;
	
	if(id){
		postURI+='/'+id;
	}
	
	$.post(postURI,$('#page>section[hash="'+hash+'"]>form').serialize(),function(response){
		$('#page>section[hash="'+hash+'"]').setBlock(response);

		if(response.status=='success'){
			if(submit==controller || submit=='cancel'){
				$('#tabs>li[for="'+hash+'"]').remove();
				$('#page>section[hash="'+hash+'"]').remove();

				var lastAccessedHash;
				var lastAccessTime=0;
				
				var sections = $('#page>section').each(function(){
					if($(this).attr('time-access')>lastAccessTime){
						lastAccessedHash=$(this).attr('hash');
						lastAccessTime=$(this).attr('time-access');
					}
				}).length
				
				if(sections>0){
					$.locationHash(lastAccessedHash);
				}else{
					$.locationHash($('#page').attr('default-uri'));
				}
			}
		}

	},'json');

	return false;
})
/*边栏提交按钮的点击事件*/
.on('click','#side-bar>aside input:submit',function(){

	$.post($(this).closest('aside').attr('for'),$(this).closest('form').serialize()+'&submit='+$(this).attr('name'),function(response){
		$(document).setBlock(response);
	},'json');
	
	return false;
})
/*分页按钮响应*/
.on('click','.pagination button',function(){
	
	$.post('/'+hash,{start:$(this).attr('target-page-start')},function(response){
		$(document).setBlock(response);
	},'json');
	
	return false;
})
/*edit表单元素更改时实时提交到后台 */
.on('change','#page>section>form:[id] :input',function(){
	var value=$(this).val();
	if($(this).is(':checkbox') && !$(this).is(':checked')){
		value=0;
	}
	var id = $('#page>section[hash="'+hash+'"]>form').attr('id');
	var name = $(this).attr('name').replace('[','/').replace(']','');
	var data={};data[name]=value;
	$.post('/'+controller+'/setfields/'+id,data);
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
			$(this).val(ui.item.label).trigger('autocompleteselect',{value:ui.item.value}).trigger('change');
			return false;
		},
		focus: function(event,ui){
			//$(this).val(ui.item.label);
			return false;
		},
		response: function(event,ui){
			if(ui.content.length==0){
				$(this).trigger('autocompletenoresult');
			}
			//$(this).trigger('change');
		}
	})
	/*.bind('input.autocomplete', function(){
		//修正firefox下中文不自动search的bug
		$(this).trigger('keydown.autocomplete'); 
	})*/
	//.autocomplete('search')
	;
})
//响应每一栏标题上的"+"并显示/隐藏添加菜单
.on('click','.item>.title>.toggle-add-form',function(){
	var addForm=$(this).closest('.item').find('.add-form');
	if(addForm.is(':hidden')){
		addForm.show(200);
		$(this).html('-');
	}else{
		addForm.hide(200);
		$(this).html('+');
	}
})
.on('enable','[display-for]:not([locked-by])',function(event){
	$(this).find(':input:disabled:not([locked-by])').trigger('change').removeAttr('disabled');
	$(this).show();

})
.on('disable','[display-for]:not([locked-by])',function(event){
	$(this).hide();
	$(this).find(':input:enabled').trigger('change').attr('disabled','disabled');

}).on('mouseenter mouseleave','.contentTable>tbody>tr',function(){
	$(this).toggleClass('highlighted');

}).on('click','.contentTable>tbody>tr:has(td:first[hash])',function(){
	$.locationHash($(this).children('td:first').attr('hash'));

}).on('click','.contentTable>tbody a, .contentTable :input',function(event){
	event.stopPropagation();

})
//标签选项卡的关闭按钮行为
.on('click','#tabs span.ui-icon-close',function() {
	var panelId = $( this ).closest( "li" ).remove().attr( "aria-controls" );
	$( "#" + panelId ).remove();
	$('#page').tabs( "refresh" );
});

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

/*扩展jQuery工具函数库*/
jQuery.showMessage=function(message,type,directExport){
	if(!directExport){
		var directExport=false;
	}

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
	
	$.processMessage();

}

jQuery.processMessage=function(){
	var noticeEdge=50;
	var lastNoticeHeight=0;
	$('.message').each(function(index,element){
		$(this).css('top',noticeEdge+lastNoticeHeight+'px');
		lastNoticeHeight+=$(this).height()+30;
	});

	$('.message').click(function(){
		$(this).stop(true).fadeOut(200,function(){
			$(this).remove();
			$.processMessage();
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

jQuery.parseMessage=function(messages){
	if(messages){
		$.each(messages,function(messageType,messages){
			$.each(messages,function(index,message){
				$.showMessage(message,messageType);
			});
		});
	}
}

/*扩展jQuery对象函数*/
jQuery.fn.showSchedule=function(event){
	var target=$(this);
	var dialog=$('<div></div>').appendTo('body')
	.dialog({
		position:{
			my:'left bottom',
			at:'right top',
			of:target
		},
		dialogClass:'shadow schedule-form',
		show:'fade',hide:'fade',
		modal:true
	}).html('<div class="throbber"><img src="/images/throbber.gif" /></div>')
	
	.dialog( "option", "buttons", [
		{
			text: "编辑",
			click: function(){
				$(this).editSchedule(event);
			}
		}
	]);

	if($('#page .contentTableBox').attr('id')=='calendar'){
		dialog.dialog('option','buttons',[{
			text:'添加至任务墙',
			click:function(){
				$.get('/schedule/addtotaskboard/'+event.id,function(){
					dialog.dialog('close');
				});
			}
		}].concat(dialog.dialog('option','buttons')));
	}else if($('#page .contentTableBox').attr('id')=='taskboard'){
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
		dialog.dialog('option','title',response.data.name);
		dialog.html(response.data.view);
	},'json');
}

jQuery.fn.createSchedule=function(startDate, endDate, allDay, project, completed){
	date = new Date();
	selection=$(this);
	var profile_count = 0;
               
	var dialog=$('<div class="dialog"></div>').appendTo('body')
	.dialog({
		position:{
			my:'left bottom',
			at:'right top',
			of:selection
		},
		dialogClass:'shadow schedule-form',
		autoOpen:true,show:'fade',hide:'fade',
		modal:true,
		close:function(){
			selection.parents('.fc:first').fullCalendar('unselect');
			$(this).remove();
		}
	}).html('<div class="throbber"><img src="/images/throbber.gif" /></div>');

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
		
		dialog.dialog( "option", "buttons", [{
			text: "+",
			click: function(){
				$(".dialog.ui-dialog-content.ui-widget-content").append('<input name="schedule_profile_name_'+profile_count+'" class="text" placeholder="信息名称" style="width:25%" /><input name="schedule_profile_content_'+profile_count+'" class="text" placeholder="信息内容" style="width:60%" />');
				profile_count++;
			}
		},
		{
			text: "保存",
			click: function() {
			if(startDate && endDate){
				var content=dialog.find('[name="content"]').val();
				var project=dialog.find('[name="project"]').val();
				var people=dialog.find('[name="people"]').val();
				var place=dialog.find('[name="schedule[place]"]').val();
				var fee=dialog.find('[name="schedule[fee]"]').val();
				var fee_name=dialog.find('[name="schedule[fee_name]"]').val();
				var paras=content.split("\n");
				var name=paras[0];
				var data={
					time_start:startDate.getTime()/1000,
					time_end:endDate.getTime()/1000,
					all_day:Number(allDay),
					content:content,
					name:name,
					"case":project
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
						showMessage('日程添加失败','warning');
						console.log(response);
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
		}]);
	},'json');
}

jQuery.fn.editSchedule=function(event){
	dialog=$(this);
	
	$.get('/schedule/edit/'+event.id,function(response){
		dialog.dialog('option','title',response.data.name).html(response.data.view).find('[name="name"]').focus();
		
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
						console.log(result);
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

jQuery.fn.getOptionsByLabelRelative=function(labelName,callback){
	var select=$(this);
	
	$.get('/label/getrelatives/'+labelName,function(response){
		var options='';
		$.map(response.data,function(item){
			options+='<option value="'+item+'">'+item+'</option>';
		})
		select.html(options).trigger('change');
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

jQuery.fn.reset=function(){
	$(this).find(':input:not(:submit):not(select)').val('');
	$(this).find(':input[default-value]').val($(this).attr('default-value'));
}

/**
 *根据一个后台返回的响应
 *（包含status, message, data属性. 其中，data为多个如下结构的对象type, content, selector, method）
 *中包含的信息，对当前页面进行部分再渲染
 *
 */
jQuery.fn.setBlock=function(response){
	
	var parent=this;
	
	if(response.status=='login_required'){
		window.location.href='login';
		return this
	}

	else if(response.status=='redirect'){
		$.locationHash(response.data);
		return this;
	}
	
	else if(response.status=='refresh'){
		$.get(hash,function(response){
			$(document).setBlock(response);
		});
	}
	
	$.parseMessage(response.message);
	
	$.each(response.data,function(dataName,data){
		
		if(data.method=='replace'){
			if(data.selector){
				parent.find(data.selector).replaceWith(data.content);
				parent.find(data.selector).trigger('blockload');
			}
		}else{
			if(data.selector){
				var block=parent.find(data.selector).html(data.content).trigger('blockload');
				
				/*如果数据是主页面内容，则标记载入时间，出发指定事件*/
				if(dataName=='content'){
					block.trigger('sectionload').attr('time-load',$.now());
				}
			}
		}
	});
	
	return this;
}