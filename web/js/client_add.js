$(function(){
	var section = $('#page>section[hash="'+hash+'"]');
	/*判别个人或单位，激活不同的表单*/
	$('[name="client[character]"]').on('change',function(){
		if($(this).is(':checked')){
			section.find('[display-for="单位"]').trigger('enable');
			section.find('[display-for="个人"]').trigger('disable');
		}else{
			section.find('[display-for="个人"]').trigger('enable');
			section.find('[display-for="单位"]').trigger('disable');
		}
	}).trigger('change');
	
	/*响应客户来源选项*/
	$('[name="source[type]"]').on('change',function(){
		if($.inArray($(this).val(),['其他网络','媒体','老客户介绍','合作单位介绍','其他'])===-1){
			$('[name="source[detail]"]').hide().attr('disabled','disabled').val('');
		}else{
			$('[name="source[detail]"]').removeAttr('disabled').show();
		}
	});
	
	$('input[name="client[birthday]"]').click(function(){
		$(this).select();
	});
	
	//根据身份证生成生日
	$('input[name="client[id_card]"]').blur(function(){
		if($(this).val().length===18){
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