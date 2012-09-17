$(function(){
	$('[name="student[resident]"]').change(function(){
		if($(this).is(':checked')){
			$('[name="student[dormitory]"]').removeAttr('disabled');
		}else{
			$('[name="student[dormitory]"]').val('').attr('disabled','disabled');
		}
	});

	//根据身份证生成生日
	$('input[name="student[id_card]"]').change(function(){
		if($(this).val().length==18){
			$('input[name="student[birthday]"]').val($(this).val().substr(6,4)+'-'+$(this).val().substr(10,2)+'-'+$(this).val().substr(12,2));
		}
	});
	
	//响应亲属添加-关系-其他选项
	$('select[name="student_relatives[relationship]"]').change(function(){
		if($(this).val()=='其他'){
			$(this).css('width','10%')
			.after('<input type="text" name="student_relatives[relationship]" style="width:9%" />');
		}else{
			$(this).css('width','20%')
			.siblings('input[name="student_relatives[relationship]"]').remove();
		}
	});
	
});
