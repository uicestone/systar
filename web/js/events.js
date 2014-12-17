$(document)
.ready(function(){
	//console.log('document ready');
	
	page=$('article');nav=$('nav');aside=$('aside');header=$('header');
	tabs=header.children('#tabs');throbber=header.children('.throbber');

	if(environment !== 'development' && username){
		setInterval(function(){
			$.get('/polling');
		},10000);
	}
	
	if(!window.location.hash.substr(1)){//start()方法尚未运行，hash尚未定义，只能直接获取
		//如果当前没有hash，那么不请求ajax，载入默认页面
		Backbone.history.start({silent:true});
		
		if(page.attr('default-uri')){
			syssh.navigate(page.attr('default-uri'),true);
		}
	}
	else{
		Backbone.history.start();
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
	
	header.on('blockload','.new-messages:not(:empty)',function(){
		$(this).clone().appendTo(page)
			.removeClass('new-messages')
			.css({zIndex:100,color:'#FFF'})
			.position({
				of:'.new-messages'
			})
			.animate({fontSize:'20em',opacity:0},1000,function(){
				$(this).remove();
			});
	});
	
	header.children('#topMenu').children('#message').children('.new-messages').trigger('blockload');
	
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
			$.closeTab($(this).parent('li').attr('hash'));
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
		var section = $(this);
		/*为表格代理绑定事件*/
		section.on('contenttableload','.contentTable',function(event){
			//console.log('contenttableload: '+$(this).attr('name'));
			$(this).children('tbody').children('tr')
				.on('mouseenter',function(){
					$(this).addClass('highlighted');
				})
				.on('mouseleave',function(){
					$(this).removeClass('highlighted');
				});
			
			$(this).children('tbody').children('tr[hash]')
				.on('click',function(){
					syssh.navigate($(this).attr('hash'),true);
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

				}

				if(buttonId){
					postURI+='/'+buttonId;
				}

				$.post(postURI,$('article>section[hash="'+hash+'"]>form').serialize());

				return false;
			});
		});
		
		/*编辑页的提交按钮点击事件，提交数据到后台，在页面上反馈数据和提示*/
		section.on('click.submit','button:submit',function(){
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
		
		/*edit表单元素更改时实时提交到后台 */
		section.children('form').on('change',':input:not(:file)',function(){
			var value=$(this).val();
			if($(this).is(':checkbox')){
				value=Number($(this).is(':checked'));
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
		
		section.find('select.chosen, .tagging').tagging();
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

				var asideSection = aside.children('section[hash="'+hash+'"]');
				//TODO 保存时会将边栏标签一并提交，临时解决
				var asideData=asideSection.find(':input:not([name="labels[]"])').serialize();

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
				$.post($(this).closest('section').attr('hash'),$(this).closest('section').find(':input').serialize()+'&submit='+$(this).attr('name'));
			}

		});
		
		section.find('select.chosen, .tagging').tagging();
		
		section.find('select.chosen[name="labels[]"]').on('change',function(event,newLabel){
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
		
		section.find('[display-for]:not([locked-by])').on('enable',function(){
			$(this).find(':input:disabled:not([locked-by])').removeAttr('disabled');
			$(this).show();

		});
		
		section.find('[display-for]:not([locked-by])').on('disable',function(){
			$(this).hide();
			$(this).find(':input:enabled').attr('disabled','disabled');

		});
		
	});
	
	!localStorage.hideBirthday && $('<img/>')
		.attr('src', 'images/Happy_Birthday_gw.png')
		.css({zIndex: 100, position: 'relative', left: '50%', marginLeft: '-20px', marginTop: '80px', width: '40px'})
		.appendTo('body')
		.animate({marginLeft: '-200px', width: '400px'}, 1000)
		.on('click', function(){
			$(this).fadeOut(3000);
			localStorage.hideBirthday = true;
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
/*分页按钮响应*/
.on('click','.pagination button',function(){

	$.post('/'+hash,{start:$(this).attr('target-page-start'),submit:'pagination'});

	return false;
})
/*toggle button文字根据change事件显示*/
.on('change',':checkbox',function(){
	var title=$(this).is(':checked')?$(this).attr('title-checked'):$(this).attr('title-unchecked');
	var text=$(this).is(':checked')?$(this).attr('text-checked'):$(this).attr('text-unchecked');
	$(this).next('.ui-button').attr('title',title);
	$(this).next('.ui-button').children('.ui-button-text').text(text);
})
.on('drop dragover', function(event){
	event.preventDefault();
});