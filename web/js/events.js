var page,nav,header,tabs,aside,hash,controller,action,username,sysname,uriSegments;

$(window).on('hashchange',function(){
	//console.log('hashchange');
	hash=$.locationHash();

	uriSegments=hash.split('/');
	
	/*根据当前hash，设置标签选项卡和导航菜单激活状态*/
	tabs.children('[for="'+hash+'"]').addClass('activated');
	tabs.children('[for!="'+hash+'"]').removeClass('activated');
	
	nav.find('li').removeClass('activated');
	nav.find('li[href="#'+hash+'"]').addClass('activated').parent('ul').show().parents('li').addClass('activated').children('.arrow').children('img').rotate(90);
	/*默认展开当前二级导航所在的子导航*/

	/*
	 *根据当前hash，显示对应标签页面，隐藏其他页面。
	 *如果当前page中没有请求的页面（或者已过期），那么向服务器发送请求，获取新的页面并添加标签选项卡。
	 */
	if(page.children('section[hash="'+hash+'"]').length>0){
		page.children('section[hash!="'+hash+'"]').hide();
		aside.children('section[for!="'+hash+'"]').hide();
	
		page.children('section[hash="'+hash+'"]').show().attr('time-access',$.now()).trigger('sectionshow');
		aside.children('section[for="'+hash+'"]').show().trigger('sidebarshow');
		
	}else{
		$.get(hash,function(response){
			//只对成功的响应生成标签选项卡、边栏和主页面元素
			if(response.status==='success'){
				page.children('section[hash!="'+hash+'"]').hide();
				aside.children('section[for!="'+hash+'"]').hide();

				$('<section hash="'+hash+'" time-access="'+$.now()+'"></section>').appendTo(page).trigger('sectioncreate');
				$('<section for="'+hash+'"></section>').appendTo(aside).trigger('sidebarcreate');
				/*如果请求的hash在导航菜单中不存在，则生成标签选项卡*/
				if(nav.find('a[href="#'+hash+'"]').length===0 && response.section_title){
					tabs.append('<li for="'+hash+'" class="activated"><a href="#'+hash+'">'+response.section_title+'</a></li>');
				}
			}
		});
	}
	
});

$(document)
.tooltip({
	tooltipClass:'pre-line',
	position:{
		my:'right bottom',
		at:'right top'
	},
	show:{
		delay:500
	}
})
.ready(function(){
	page=$('article');nav=$('nav');aside=$('aside');header=$('header');
	tabs=header.children('#tabs');throbber=header.children('.throbber');
	
	/*老浏览器警告*/
	if($.browser.msie && ($.browser.version<8 || document.documentMode && document.documentMode<8)){
		$.showMessage('您正在使用不被推荐的浏览器，请关闭浏览器兼容模式。如果问题仍然存在，<a href="/browser">请点此下载推荐的浏览器</a>','warning');
	}
	
	/*为主体载入指定页面或默认页面*/
	if(window.location.hash){
		$(window).trigger('hashchange');
	}else if(page.attr('default-uri')){
 		$.locationHash(page.attr('default-uri'));
 	}
	
	/*导航栏配置*/
	nav.find('a').click(function(event){
		if($(this).hasClass('add')){
			/*点击添加按钮的时候不触发外层li的点击事件*/
			event.stopPropagation();
		}else{
			/*若点到链接，阻止默认动作，触发外层li的点击事件*/
			event.preventDefault();
		}
	});
	
	/*将点击事件绑定在li上，从而整个菜单都可以响应点击*/
	nav.find('li').click(function(event){
		event.stopPropagation();
		window.location.href=$(this).attr('href');
		$(this).children('ul:hidden').show();
		$(this).children('.arrow').children('img').rotate({animateTo:90,duration:200});

		/*重复点击当前导航：手动刷新*/
		if($(this).children('a').attr('href').substr(1)===hash){
			$.refresh(hash);
		}
	});
	
	/*二级菜单展开*/
	nav.children('ul').children('li').children('.arrow').click(function(event){
		event.stopPropagation();
		var subMenu=$(this).siblings('[level="1"]');
		if(subMenu.is(':hidden')){
			subMenu.show(200);
			$(this).children('img').rotate({animateTo:90,duration:500});
		}else{
			$(this).children('img').rotate({animateTo:0,duration:500});
			subMenu.hide(200);
		}
	});
	
	tabs
	/*标签手动刷新*/
	.on('click','a[href^="#"]',function(){
		if($(this).attr('href').substr(1)===hash){
			$.get(hash);
		}
	})
	/*标签选项卡上的关闭按钮*/
	.on('mouseenter','li',function(){
		$('<span/>',{'class':'icon-close'}).appendTo(this)
		.click(function(){
			$.closeTab($(this).parent('li').attr('for'));
		});
	})
	.on('mouseleave','li',function(){
		$(this).children('span.icon-close').remove();
	});
	
	page
	/*主页面被切换至，或被加载时，都要重新设置文档标题*/
	.on('sectionload sectionshow','section',function(){
		document.title=affair+' - '+(username?username+' - ':'')+sysname;
	})
	.on('sectioncreate','section',function(){
		//console.log('section create');
		var section = $(this);
		/*为表格代理绑定事件*/
		section.on('contenttableload','.contentTable',function(event){
			//console.log('contenttableload: '+$(this).attr('name'));
			$(this).children('tbody').children('tr[hash]')
				.on('click',function(){
					$.locationHash($(this).attr('hash'));
				})
				.find('a, :input')
					.on('click',function(event){
						event.stopPropagation();
					});
			
			$(this).children('tbody').children('tr')
				.mouseenter(function(){
					//console.log('mouseenter tr');
					$(this).children('td:first').children('.hover').show();
				})
				.mouseleave(function(){
					$(this).children('td:first').children('.hover').hide();
				});
			
			$(this).find('button:submit').off('.submit').on('click.submit',function(){
				var form = section.children('form');

				var id = section.find('form[name="'+controller+'"]').attr('id');
				var buttonId = $(this).attr('id');
				var submit = $(this).attr('name').replace('submit[','').replace(']','');

				var postURI='/'+controller+'/submit/'+submit;

				if(id){
					postURI+='/'+id;

					if(buttonId){
						postURI+='/'+buttonId;
					}

				}

				$.post(postURI,$('article>section[hash="'+hash+'"]>form').serialize());

				return false;
			});
		});
	})
	.on('sectionload','section',function(){
		//console.log('section load');
		
		var section = $(this);

		/*每一栏标题上的"+"并显示/隐藏添加菜单*/
		section.find('.item>.toggle-add-form').on('click',function(){
			var addForm=$(this).siblings('.add-form');
			if(addForm.is(':hidden')){
				addForm.fadeIn(200);
				$(this).html('－');
			}else{
				addForm.fadeOut(200);
				$(this).html('＋');
			}
		});
		
		/*编辑页的提交按钮点击事件，提交数据到后台，在页面上反馈数据和提示*/
		section.find('button:submit').on('click.submit',function(){
			var form = section.children('form');
			var button = $(this);

			var id = section.find('form[name="'+controller+'"]').attr('id');
			var buttonId = $(this).attr('id');
			var submit = $(this).attr('name').replace('submit[','').replace(']','');

			var postURI='/'+controller+'/submit/'+submit;

			if(id){
				postURI+='/'+id;
				
				if(buttonId){
					postURI+='/'+buttonId;
				}
				
			}

			$.post(postURI,$('article>section[hash="'+hash+'"]>form').serialize(),function(response){
				/*添加表单的提交按钮 清空表单*/
				
				/*如果被点击的按钮在一个sublist的add-form里面，那么重置这个add-form*/
				if(response.status==='success'){
					button.closest('.add-form').reset();
				}
		
			});

			return false;
		});
		
		/*edit表单元素更改时实时提交到后台 */
		section.children('form').on('change',':input:not(:file)',function(){
			var value=$(this).val();
			if($(this).is(':checkbox') && !$(this).is(':checked')){
				value=0;
			}
			var id = section.children('form').attr('id');
			var name = $(this).attr('name').replace(/\[(.+?)\]/,'/$1');
			var data={};data[name]=value;
			
			if(controller){
				var uri='/'+controller+'/setfields';
				if(id){
					uri+='/'+id;
				}

				/*这里不期望返回json数据，由于$.post被重写，默认dataType为json，因此需要手动指定dataType*/
				$.post(uri,data,'post');
			}
		});
		
		section.find('[display-for]:not([locked-by])').on('enable',function(){
			$(this).find(':input:disabled:not([locked-by])').removeAttr('disabled');
			$(this).show();

		});
		
		section.find('[display-for]:not([locked-by])').on('disable',function(){
			$(this).hide();
			$(this).find(':input:enabled').attr('disabled','disabled');

		});
				
		section.find('.contentTable').trigger('contenttableload');
		
		section.find('.contentTable>tbody>tr').on('mouseenter mouseleave',function(){
			$(this).toggleClass('highlighted');
		
		});
				
		section.find('select.chosen').each(function(index,object){
			var options={dropdownCss:{minWidth:100}};
			if($(object).is('.allow-new')){
				$.extend(options,{
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
			
			$(object).select2(options);
		});
		
		if(hash==='schedule/taskboard'){
	
			section.find( ".sortable.column" ).sortable({
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
						$.post('/schedule/writecalendar/update/'+senderId,{in_todo_list:0});
					}
				}
			}).disableSelection();
			
			section.on('click','.portlet',function(){
				$.viewSchedule({id:$(this).attr('event-id'),target:this,taskboard:section});
			});

			section.on('click','.portlet-header .ui-icon',function(event){
				event.stopPropagation();
				$(this).toggleClass( 'ui-icon-minusthick' ).toggleClass( 'ui-icon-plusthick' );
				$(this).parents( '.portlet:first' ).find( '.portlet-content' ).toggle();
			});

		}
		
	});
	
	aside
	.on('sidebarload','section',function(){
		var section=$(this);
		/*边栏普通提交按钮（提交给当前page地址，以刷新page）*/
		section.find('button:submit').on('click',function(event){
			
			event.preventDefault();
			
			if($(this).is('[name^="submit"]')){
				/*边栏主要提交按钮（提交到controller/submit/{submit_name}/{item_id}）*/
				var pageSection = page.children('section[hash="'+hash+'"]');
				var form = pageSection.children('form');
				var formData=form.serialize();

				var asideSection = aside.children('section[for="'+hash+'"]');
				var asideData=asideSection.find(':input').serialize();

				var id = form.attr('id');
				var submit = $(this).attr('name').replace('submit[','').replace(']','');

				var postURI='/'+controller+'/submit/'+submit;

				if(id){
					postURI+='/'+id;
				}

				$.post(postURI,formData+'&'+asideData,function(response){
					if(response.status==='close'){
						$.closeTab(hash);
					}
				});
			}
			else{
				$.post($(this).closest('section').attr('for'),$(this).closest('section').find(':input').serialize()+'&submit='+$(this).attr('name'));
			}

		});
		
		section.find('select.chosen').each(function(index,object){
			var options={dropdownCss:{minWidth:100}};
			if($(object).is('.allow-new')){
				$.extend(options,{
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
			
			$(object).select2(options)
			.on('change',function(event,newLabel){
				var id=page.children('[hash="'+hash+'"]').children('form').attr('id');
				var label,method;
				
				if(newLabel){
					label=newLabel;
					method='add';
				}else if(event.added && id){
					label=event.added.id;
					method='add';
				}else if(event.removed && id){
					label=event.removed.id;
					method='remove';
				}
				
				if(method && id){
					$.post('/'+controller+'/'+method+'label/'+id,{label:label});
				}
				
			});
		});
		
		section.find('[display-for]:not([locked-by])').on('enable',function(){
			$(this).find(':input:disabled:not([locked-by])').removeAttr('disabled');
			$(this).show();

		});
		
		section.find('[display-for]:not([locked-by])').on('disable',function(){
			$(this).hide();
			$(this).find(':input:enabled').attr('disabled','disabled');

		});
		
		if(hash==='schedule/taskboard'){
			section.find('.draggable.portlet').draggable({
				helper: 'clone',
				connectToSortable:'.sortable.column'
			});

			section.on('click','.portlet',function(){
				$.viewSchedule({id:$(this).attr('event-id'),target:this,taskboard:section});
			});

			section.on('click','.portlet-header .ui-icon',function(event){
				event.stopPropagation();
				$(this).toggleClass( 'ui-icon-minusthick' ).toggleClass( 'ui-icon-plusthick' );
				$(this).parents( '.portlet:first' ).find( '.portlet-content' ).toggle();
			});
		}
				
	});

})
/*主体页面加载事件*/
.on('blockload','*',function(event){
	
	/*
	 * 区域加载后要一次性执行的代码
	 * 都通过find来执行，因此不需要事件冒泡，避免重复执行
	 */
	event.stopPropagation();
	$(this).find('[placeholder]').placeholder();
	$(this).find('.date[type="text"]').datepicker();
	$(this).find('.datetime[type="text"]').datetimepicker();
	$(this).find('.birthday[type="text"]').datepicker({
		changeMonth: true,
		changeYear: true
	});
})
/*自动完成*/
.on('focus.autocomplete','[autocomplete-model]',function(){
	var autocompleteModel=$(this).attr('autocomplete-model');
	$(this).autocomplete({
		source: function(request, response){
			$.post('/'+autocompleteModel+'/match',{term:request.term},function(responseJSON){
				response(responseJSON.data);
			});
		},
		select: function(event,ui){
			$(this).val(ui.item.label).trigger('autocompleteselect',{value:ui.item.value}).trigger('change');
			return false;
		},
		focus: function(event,ui){
			//$(this).val(ui.item.label);
			return false;
		}
	});
})
/*分页按钮响应*/
.on('click','.pagination button',function(){

	$.post('/'+hash,{start:$(this).attr('target-page-start'),submit:'pagination'});

	return false;
})
/*toggle button文字根据change事件显示*/
.on('change',':checkbox',function(){
	var title=$(this).is(':checked')?$(this).attr('title-checked'):$(this).attr('title-unchecked');
	/*@TODO tooltip text not changing after label title changed*/
	$(this).next('.ui-button').attr('title',title);
})
.on('drop dragover', function(event){
	event.preventDefault();
});