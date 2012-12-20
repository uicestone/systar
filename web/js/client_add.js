$(function(){
	$('[name="client[classification]"]').change(function(){
		$('[name="client[type]"]').getOptions('client',$(this).val());
	});
	
	$('select[name="source[type]"]').change(function(){
		//响应客户来源选项
		if(jQuery.inArray($(this).val(),['其他网络','媒体','老客户介绍','合作单位介绍','其他'])==-1){
			$('[name="source[detail]"]').attr('disabled','disabled').val('');
		}else{
			$('[name="source[detail]"]').removeAttr('disabled');
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
});