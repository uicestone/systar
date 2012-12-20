$(function(){
	if($.browser.msie && $.browser.version<7 && action!='user_browser'){
		window.location.href='/user/browser';
	}

	window.parent.document.title=affair+' - '+(username?username+' - ':'')+sysname;

	processMessage();

	$('.minimize-button').click(function(){
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
	});
	
	//如果不是顶层框架，则
	if(window!=window.parent){
		//设置导航菜单高亮
		$(window.parent.navFrame.document).find('#navMenu').find('li#nav-'+controller).addClass('activated').siblings('li').removeClass('activated');
		$(window.parent.navFrame.document).find('#navMenu').find('ul.l1').find('li').removeClass('activated').parent().parent().parent().find('li#nav-'+controller+'-'+action).addClass('activated');
		//设置顶层框架的hash为当前框架的URI
		//window.parent.location.hash='#'+location.pathname.substr(1)+location.search;
	}

	if(action=='add' || action=='edit'){
		//对于add和edit页面，当鼠标进入submit按钮的时候记录当前页面滚动条位置
		$.post('/misc/getsession/scroll',{controller:controller,method:action},function(scrollTop){
			$(window).scrollTop(scrollTop);
		});
		
		$('[name^="submit"]').mouseenter(function(){
			var scrollTop=$(window).scrollTop();
			$.post('/misc/setsession/scroll',{controller:controller,method:action,scrollTop:scrollTop});
		});
	}
	
	if(!$.browser.msie){
		$('.contentTable:not(.search-bar)').children('tbody').children('tr').each(function(index){
			$(this).delay(15*index).css('opacity',0).css('visibility','visible').animate({opacity:'1'},500);
		});
	}
	
	$('select.filter[method!="get"]').change(function(){
		//边栏选框自动提交
		post($(this).attr('name'),$(this).val());
	});
	
	$('select.filter[method="get"]').change(function(){
		redirectPara($(this));
	});

	$('.date').datepicker();
	
	$('.birthday').datepicker({
		changeMonth: true,
		changeYear: true,
		defaultDate:'1997-1-1'
	});
	
	//案下客户名称自动完成
	$('[autocomplete-model=client]').autocomplete({
		source: function(request, response){
			$.post('/client/match',{term:request.term},function(data){
				response(data);
			},'json');
		},
		select: function(event,ui){
			$(this).val(ui.item.label)
			.siblings('[name="'+$(this).attr('autocomplete-input-name')+'"]').val(ui.item.value);
			return false;
		},
		focus: function(event,ui){
			$(this).val(ui.item.label)
			.siblings('[name="'+$(this).attr('autocomplete-input-name')+'"]');
			return false;
		},
		response: function(event,ui){
			if(ui.content.length==0){
				$('[display-for~="new"]').trigger('enable');
			}else{
				$('[display-for~="new"]').trigger('disable');
			}
		}
	})
	/*.bind('input.autocomplete', function(){
		//修正firefox下中文不自动search的bug
		$(this).trigger('keydown.autocomplete'); 
	})*/
	.autocomplete('search');

	$('[placeholder]').placeholder()
	
	$('.item>.title>.toggle-add-form').click(function(){
		//响应每一栏标题上的"+"并显示/隐藏添加菜单
		var form=$(this).parent().siblings('.add-form');
		if(form.is(':hidden')){
			form.show(200);
			$(this).html('-');
		}else{
			form.hide(200);
			$(this).html('+');
		}
	});
	
	//多项表单添加按钮提示
	/*$('.inputTable').find('.contentTable').siblings('[id$="AddForm"]').children('input:submit').qtip({
		content:'点这里来保存这一条信息。你还可以添加更多哦',
		position:{
			my:'top right'
		}
	})
	.parent()
	.mouseenter(function(){
		$(this).children('input:submit').qtip('show');
	}).mouseleave(function(){
		$(this).children('input:submit').qtip('hide');
    });*/

	/**
	 * edit表单元素更改时实时提交到后台
	 */
	$('form[name="'+controller+'"]').find('input,select').change(function(){
		var value=$(this).val();
		if($(this).is(':checkbox') && !$(this).is(':checked')){
			value=0;
		}
		var id = $('form[name="'+controller+'"]').attr('id');
		var name = $(this).attr('name').replace('[','/').replace(']','');
		$.post('/'+controller+'/setfield/'+id+'/'+name,{value:value});
	});
	
	/**
	 * edit表单提交事件
	 */
	$('form[name="'+controller+'"]').find('input:submit').click(function(){
		var id = $('form[name="'+controller+'"]').attr('id');
		var submit = $(this).attr('name').replace('submit[','').replace(']','');
		
		$.post('/'+controller+'/submit/'+submit+'/'+id,$('form[name="'+controller+'"]').serialize(),function(response){
			if(response=='success'){
				$.get('/case/asa/html',function(html){
					$('contentTable').html(html);
				});
			}else{
				showMessage(response,'warning');
			}
		});
		return false;
	});
	
	$('[display-for]').hide();
	
	$('[display-for]').on('enable',function(){
		$(this).show();
		if($(this).is('input,select')){
			$(this).removeAttr('disabled');
		}
		$(this).find('input,select').removeAttr('disabled');
	});
	
	$('[display-for]').on('disable',function(){
		$(this).hide();
		if($(this).is('input,select')){
			$(this).attr('disabled','disabled');
		}
		$(this).find('input,select').attr('disabled','disabled');
	});
	
});
