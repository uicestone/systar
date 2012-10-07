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
	
function createDialog(title){
	//$('#dialog').remove();//创建一个对话框的时候，先删除其他对话框
	var dialog=$('<div id="dialog"></div>').appendTo(document)
	.dialog({
		title:title,autoOpen:false,position:['middle', 200],minHeight:300,minWidth:500,show:'fade',hide:'fade',close:function(){$(this).remove()}
	});
	
	return dialog;
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

jQuery.fn.serializeJSON=function() {
	var json = {};
	jQuery.map($(this).serializeArray(), function(n, i){
		json[n['name']] = n['value'];
	});
	return json;
};

jQuery.parseResponse=function(response){
	try{
		return $.parseJSON(response);
	}catch(e){
		showMessage(response,'warning');
	}
}

var currentWindow=window;
while(typeof currentWindow.opener !=='undefined' && currentWindow.opener!==null){
	currentWindow=currentWindow.opener;
}
window.rootOpener=currentWindow;