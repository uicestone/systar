$(function(){
	var section = page.children('section[hash="'+hash+'"]');
	
	section.find('#active').button().change();
	
	/*根据案件分类显示/隐藏案件阶段选项*/
	section.find('[name="labels[分类]"]')
	.on('change',function(){
		if($(this).val()==='争议'){
			$(this).siblings('[name="labels[阶段]"]').removeAttr('disabled').show();
		}else{
			$(this).siblings('[name="labels[阶段]"]').hide().attr('disabled','disabled');
		}
	});
	
	/*人员子表的删除行按钮*/
	section.find('.item[name="people"]:not([locked])')
		.on('mouseenter','tbody>tr',function(){
	
			$(this).siblings('tr').each(function(){
				if($(this).data('delete-button')){
					$(this).data('delete-button').remove();
				}
			});
	
			var that=$(this).data('delete-button',
				$('<button/>',{text:'删除',type:'submit',name:'submit[remove_people]',id:$(this).attr('id')})
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
						$.post('/'+controller+'/submit/remove_people/'+project+'/'+people,function(){
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

	/*客户子表的删除行按钮*/
	section.find('.item[name="client"]:not([locked])')
		.on('mouseenter','tbody>tr',function(){
	
			$(this).siblings('tr').each(function(){
				if($(this).data('delete-button')){
					$(this).data('delete-button').remove();
				}
			});
	
			var that=$(this).data('delete-button',
				$('<button/>',{text:'删除',type:'submit',name:'submit[remove_client]',id:$(this).attr('id')})
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
						$.post('/'+controller+'/submit/remove_client/'+project+'/'+people,function(){
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

	/*文档子表的删除行按钮*/
	section.find('.item[name="document"]:not([locked])')
		.on('mouseenter','tbody>tr',function(){
	
			$(this).siblings('tr').each(function(){
				if($(this).data('delete-button')){
					$(this).data('delete-button').remove();
				}
			});
	
			var that=$(this).data('delete-button',
				$('<button/>',{text:'删除',type:'submit',name:'submit[remove_document]',id:$(this).attr('id')})
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
						var document=that.closest('tr').attr('id');
						$.post('/'+controller+'/submit/remove_document/'+project+'/'+document,function(){
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

	/*子表的删除行按钮*/
	section.find('.item[name="staff"]:not([locked])')
		.on('mouseenter','span[role]',function(){
	
			$(this).closest('tbody').find('span[role]').each(function(){
				if($(this).data('delete-button')){
					$(this).data('delete-button').remove();
				}
			});
	
			var that=$(this).data('delete-button',
				$('<button/>',{text:'x'}).appendTo('body')
					.position({
						my:'right bottom',
						at:'right top',
						of:$(this)
					})
					.hide()
					.on('mouseenter',function(){
						$(this).clearQueue();
					})
					.on('mouseleave',function(){
						$(this).stop().remove();
					})
					.on('click',function(){
						var project=that.closest('form[id]').attr('id');
						var people=that.closest('tr').attr('id');
						var role=that.attr('role');
						$.post('/'+controller+'/removepeoplerole/'+project+'/'+people,{role:role},function(){
							that.data('delete-button').remove();
						});
					}).delay(100).fadeIn()
				);
		})
		.on('mouseleave','span[role]',function(){
			$(this).data('delete-button').clearQueue().delay(200).hide(0,function(){
				$(this).remove();
			});
		});

	/*客户添加表单－客户名称自动完成事件的响应*/
	section.find('.item[name="client"]')
	.on('autocompleteselect',function(event,data){
		/*有自动完成结果且已选择*/
		$(this).find('[name="case_client[client]"]').val(data.value).trigger('change');

		$(this).find('[display-for~="new"]').trigger('disable');
	})
	.on('autocompleteresponse',function(){
		/*自动完成响应*/
		$(this).find('[display-for~="new"]:hidden').trigger('enable');
		$(this).find('[name="case_client[client]"]').val('').trigger('change');
	});
	
	section.find('[name="client[name]"]').focus(function(){
		$(this).select();
	});

	/*案下客户类别联动*/
	section.find('[name="client[type]"]').on('show change',function(){
		
		var addForm=$(this).parents('.add-form:first');

		if($(this).val()==='contact'){
			addForm.find('[name="client_labels[类型]"]').hide().attr('disabled','disabled');
		}else{
			addForm.find('[name="client_labels[类型]"]').removeAttr('disabled').show();
			$(this).siblings('[name="client_labels[类型]"]').getOptionsByLabelRelative($(this).find('option:selected').text());
		}

		if($(this).val()==='client'){
			addForm.find('[display-for~="client"]').trigger('enable');
			addForm.find('[display-for~="non-client"]').trigger('disable');
		}else{
			addForm.find('[display-for~="client"]').trigger('disable');
			addForm.find('[display-for~="non-client"]').trigger('enable');
		}

	});

	//响应客户来源选项
	section.find('[name="client_profiles[来源类型]"]').on('change',function(){
		if($.inArray($(this).val(),['其他网络','媒体','老客户介绍','合作单位介绍','其他'])===-1){
			$('[name="client_profiles[来源]"]').hide().attr('disabled','disabled').val('');
		}else{
			$('[name="client_profiles[来源]"]').removeAttr('disabled').show();
		}
	});
	
	/*人员角色删除*/
	section.find('.item[name="people"]:not([locked]), .item[name="staff"]:not([locked])')
		.on('mouseenter','span[role]',function(){
	
			$(this).closest('tbody').find('span[role]').each(function(){
				if($(this).data('delete-button')){
					$(this).data('delete-button').remove();
				}
			});
	
			var that=$(this).data('delete-button',
				$('<button/>',{text:'x'}).appendTo('body')
					.position({
						my:'right bottom',
						at:'right top',
						of:$(this)
					})
					.hide()
					.on('mouseenter',function(){
						$(this).clearQueue();
					})
					.on('mouseleave',function(){
						$(this).stop().remove();
					})
					.on('click',function(){
						var project=that.closest('form[id]').attr('id');
						var people=that.closest('tr').attr('id');
						var role=that.attr('role');
						var uri='/'+controller+'/removepeoplerole/'+project+'/'+people;
						$.post(uri,{role:role},function(){
							that.data('delete-button').remove();
						});
					}).delay(100).fadeIn()
				);
		})
		.on('mouseleave','span[role]',function(){
			$(this).data('delete-button').clearQueue().delay(200).hide(0,function(){
				$(this).remove();
			});
		});

	/*职员添加表单－职员名称自动完成事件的响应*/
	section.find('.item[name="staff"]').on('autocompleteselect',function(event,data){
		/*有自动完成结果且已选择*/
		$(this).find('[name="staff[id]"]').val(data.value).trigger('change');
	}).on('autocompleteresponse',function(){
		$(this).find('[name="staff[id]"]').val('').trigger('change');
	});
	
	section.find('.item[name="people"]').on('autocompleteselect',function(event,data){
		/*有自动完成结果且已选择*/
		$(this).find('[name="people[id]"]').val(data.value).trigger('change');
	}).on('autocompleteresponse',function(){
		$(this).find('[name="people[id]"]').val('').trigger('change');
	});
	
	//审核按钮的触发
	section.find('button[name="submit[review]"]').click(function(){
		$(this)
		.after('<button type="submit" name="submit[send_message]">退回</button>')
		.after('<button type="submit" name="'+$(this).attr('name')+'">通过</button>')
		.after('<input type="text" name="review_message" />')
		.remove();
	});

	//“忽略”按钮的显示和隐藏
	section.find('[name^="case_fee_check"]').change(function(){
		if($('[name^="case_fee_check"]:checked').size()){
			$('[name="submit[case_fee_review]"]').removeAttr('disabled').fadeIn(200);
		}else{
			$('[name="submit[case_fee_review]"]').attr('disabled','disabled').fadeOut(200);
		}
	});

	section.find(':input:file[name="document"]').fileupload({
        dataType: 'json',
        done: function (event, data) {
			$(document.body).setBlock(data.result);
			$(this).siblings('[name="document[id]"]').val(data.result.data.id);
			$(this).siblings('[name="document[name]"]').val(data.result.data.name);
        },
		dropZone:section.find('.item[name="document"]').children('.add-form')
    });

	//案下文件类别选择'其他'时,显示输入框
	section.find('[name="case_document[doctype]"]').change(function(){
		if($(this).val()==='其他'){
			$(this).css('width','7%').after('<input type="text" name="case_document[doctype_other]" style="width:8%" />');
		}else{
			$(this).css('width','15%').siblings('input[name="case_document[doctype_other]"]').remove();
		}
	});
	
});