$(function(){
	if($.browser.msie && $.browser.version<7 && action!='user_browser'){
		window.location.href='/user?browser';
	}

	window.parent.document.title=affair+' - '+(username?username+' - ':'')+sysname;

	processMessage();

	$('.minimize-button').click(function(){
		$('#toolBar').toggleClass('minimized');
		var minimized=0;
		if($('#toolBar').hasClass('minimized')){
			minimized=1;
		}
		$.post('misc?set_session',{minimized:minimized},function(result){
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
		$(window.parent.navFrame.document).find('#navMenu').find('ul.l1').find('li').removeClass('activated').parent().parent().parent().find('li#nav-'+action).addClass('activated');
		//设置顶层框架的hash为当前框架的URI
		//window.parent.location.hash='#'+location.pathname.substr(1)+location.search;
	}

	if(action.match('[^_]+?$')=='add'){
		//对于add和edit页面，当鼠标进入submit按钮的时候记录当前页面滚动条位置
		$.post('misc?get_session&var=scroll',{controller:controller,action:action},function(scrollTop){
			$(window).scrollTop(scrollTop);
		});
		
		$('[name^="submit"]').mouseenter(function(){
			var scrollTop=$(window).scrollTop();
			$.post('misc?set_session&scroll',{controller:controller,action:action,scrollTop:scrollTop});
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
	$('[autocomplete=client]').autocomplete({
		source: function(request, response){
			$.post('client?autocomplete',{term:request.term},function(data){
				data=$.parseResponse(data);
				response(data);
			});
		},
		select: function(event,ui){
			$(this).val(ui.item.label)
			.siblings('[name="'+$(this).attr('autocomplete-input-name')+'"]').val(ui.item.value);
			return false;
		},
		focus: function(event,ui){
			$(this).val(ui.item.label);
			return false;
		},
		response: function(event,content){
			if(content.length==0){
				$('.autocomplete-no-result-menu').show().children('input,select').removeAttr('disabled');
			}else{
				$('.autocomplete-no-result-menu').hide().children('input,select').attr('disabled','disabled');
			}
		}
	}).bind('input.autocomplete', function(){
		//修正firefox下中文不自动search的bug
		$(this).trigger('keydown.autocomplete'); 
	})/*.after(function(){
		return '<input name="'+$(this).attr('autocomplete-input-name')+'" disabled="disabled" style="display:none" />';
	})*/.autocomplete('search');

	$('[placeholder]').placeholder();

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
});