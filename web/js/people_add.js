$(function(){
	var section = page.children('section[hash="'+hash+'"]');

	/*判别个人或单位，激活不同的表单*/
	section.find('[name="people[character]"]').on('change',function(){
		var character=$(this).is(':checked')?'单位':'个人';
		$.post(hash,{character:character},function(response){
			section.setBlock(response);
		});
	});
	
	/*相关人子表的删除行按钮*/
	section.find('.item[name="relative"]:not([locked])')
		.on('mouseenter','tbody>tr:not([locked])',function(){
	
			$(this).siblings('tr').each(function(){
				if($(this).data('delete-button')){
					$(this).data('delete-button').remove();
				}
			});
	
			var that=$(this).data('delete-button',
				$('<button/>',{text:'删除',type:'submit',name:'remove_relative','class':'hover',id:$(this).attr('id'),style:'position:absolute'})
					.appendTo($(this).children('td:last'))
					.position({
						my:'right-5 center',
						at:'right center',
						of:$(this)
					})
					.on('mouseenter',function(){
						$(this).clearQueue();
					})
					.on('mouseleave',function(){
						$(this).stop().remove();
					})
					.on('click',function(){
						var project=that.closest('form[id]').attr('id');
						var people=that.closest('tr').attr('id');
						$.post('/'+controller+'/submit/remove_relative/'+project+'/'+people,function(){
							that.data('delete-button').remove();
						});
						return false;
					})
				);
		})
		.on('mouseleave','tbody>tr',function(){
			$(this).data('delete-button').clearQueue().hide(0,function(){
				$(this).remove();
			});
		});

	/*资料项子表的删除行按钮*/
	section.find('.item[name="profile"]:not([locked])')
		.on('mouseenter','tbody>tr',function(){
	
			$(this).siblings('tr').each(function(){
				if($(this).data('delete-button')){
					$(this).data('delete-button').remove();
				}
			});
	
			var that=$(this).data('delete-button',
				$('<button/>',{text:'删除',type:'submit',name:'submit[remove_profile]','class':'hover',id:$(this).attr('id')})
					.appendTo($(this).children('td:last'))
					.position({
						my:'right-5 center',
						at:'right center',
						of:$(this)
					})
					.on('mouseenter',function(){
						$(this).clearQueue();
					})
					.on('mouseleave',function(){
						$(this).stop().remove();
					})
					.on('click',function(){
						var project=that.closest('form[id]').attr('id');
						var people=that.closest('tr').attr('id');
						$.post('/'+controller+'/submit/remove_profile/'+project+'/'+people,function(){
							that.data('delete-button').remove();
						});
						return false;
					})
				);
		})
		.on('mouseleave','tbody>tr',function(){
			$(this).data('delete-button').clearQueue().hide(0,function(){
				$(this).remove();
			});
		});

	/*响应客户来源选项*/
	section.find('[name="profiles[来源类型]"]').on('change',function(){
		if($.inArray($(this).val(),['其他网络','媒体','老客户介绍','合作单位介绍','其他'])===-1){
			$(this).siblings('[name="profiles[来源]"]').hide().attr('disabled','disabled').val('');
		}else{
			$(this).siblings('[name="profiles[来源]"]').removeAttr('disabled').show();
		}
	});
	
	section.find('input[name="people[birthday]"]').click(function(){
		$(this).select();
	});
	
	//根据身份证生成生日
	section.find('input[name="people[id_card]"]').blur(function(){
		if($(this).val().length===18){
			$('input[name="people[birthday]"]').val($(this).val().substr(6,4)+'-'+$(this).val().substr(10,2)+'-'+$(this).val().substr(12,2));
		}
	});
	
	/*相关人添加表单－相关人名称自动完成事件的响应*/
	section.find('.item[name="relative"]')
	.on('autocompleteselect',function(event,data){
		/*有自动完成结果且已选择*/
		$(this).find('[name="relative[id]"]').val(data.value).trigger('change');

		$(this).find('[display-for~="new"]').trigger('disable');
	})
	.on('autocompleteresponse',function(){
		/*自动完成响应*/
		$(this).find('[display-for~="new"]').trigger('enable');
		$(this).find('[name="relative[id]"]').val('').trigger('change');
	});
	
});