$(function(){
	/*响应客户来源选项*/
	$('[name="source[type]"]').on('change',function(){
		if($.inArray($(this).val(),['其他网络','媒体','老客户介绍','合作单位介绍','其他'])==-1){
			$('[name="source[detail]"]').hide().attr('disabled','disabled').val('');
		}else{
			$('[name="source[detail]"]').removeAttr('disabled').show();
		}
	});
	
	//根据来源和来源人生成来源律师
	$('[name="source[detail]"]').blur(function(){
		if($('[name="source[type]"]').val()=='老客户介绍'){
			$.post('/client/getsourcelawyer',{client_name:$(this).val()},function(source_lawyer_name){
				$('[name="client_extra[source_lawyer_name]"]').val(source_lawyer_name);
			});
		}
	});
	
	$('input[name="client[birthday]"]').click(function(){
		$(this).select();
	});
	
	//根据身份证生成生日
	$('input[name="client[id_card]"]').blur(function(){
		if($(this).val().length==18){
			$('input[name="client[birthday]"]').val($(this).val().substr(6,4)+'-'+$(this).val().substr(10,2)+'-'+$(this).val().substr(12,2));
		}
	});
	
	/*相关人添加表单－相关人名称自动完成事件的响应*/
	$('.item[name="relative"]').on('autocompleteselect',function(event,data){
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