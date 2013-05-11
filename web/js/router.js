var page,nav,header,tabs,aside,calendar,
	hash,controller,method,username,sysname,uriSegments,polling={},environment;

var Workspace = Backbone.Router.extend({
	routes: {
		'(:controller)(/:method)(/:para1)(/:para2)(/:para3)':'common'
	},
	
	common: function(){
		hash=window.location.hash.substr(1);
		//console.log(hash);
		
		uriSegments=hash.split('/');
		
		for(var key in polling){
			window.clearInterval(polling[key]);
		}

		/*根据当前hash，设置标签选项卡和导航菜单激活状态*/
		tabs.children('[hash="'+hash+'"]').addClass('activated');
		tabs.children('[hash!="'+hash+'"]').removeClass('activated');

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
			aside.children('section[hash="'+hash+'"]').show().trigger('sidebarshow');

		}else{
			$.get(hash,function(response){
				//只对成功的响应生成标签选项卡、边栏和主页面元素
				if(response.status==='success'){
					page.children('section[hash!="'+hash+'"]').hide();
					aside.children('section[for!="'+hash+'"]').hide();

					$('<section hash="'+hash+'" time-access="'+$.now()+'"></section>').appendTo(page).trigger('sectioncreate');
					$('<section hash="'+hash+'"></section>').appendTo(aside).trigger('sectioncreate').trigger('sidebarcreate');
					/*如果请求的hash在导航菜单中不存在，则生成标签选项卡*/
					if(nav.find('a[href="#'+hash+'"]').length===0 && response.section_title){
						tabs.append('<li hash="'+hash+'" class="activated"><a href="#'+hash+'">'+response.section_title+'</a></li>');
					}
				}
			});
		}
	}
});

var syssh = new Workspace;