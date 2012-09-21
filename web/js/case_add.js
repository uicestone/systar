$(function(){

	//案件类别联动
	/*$('[name="case[classification]"]').change(function(){
		$('[name="case[type]"]').getOptions('case',$(this).val());
	});*/
	
	//案下客户类别联动
	$('[name="case_client_extra[classification]"]').change(function(){
		$('[name="case_client_extra[type]"]').getOptions('client',$(this).val());
	});
	
	//响应每一栏标题上的"+"并显示/隐藏添加菜单
	$('#caseClientAdd,#caseLawyerAdd').click(function(){
		var form=$('#'+$(this).attr('id')+'Form');
		if(form.is(':hidden')){
			form.show(200);
			$(this).html('-');
		}else{
			form.hide(200);
			$(this).html('+');
		}
	});

	//响应案下客户的本案地位的"其他"选项
	$('select[name="case_client[role]"]').change(function(){
		if($(this).val()=='其他'){
			$(this).css('width','7%').after('<input style="width:8%" name="case_client[role]" type="text" />');
		}else{
			$(this).css('width','15%');
			$('input[name="case_client[role]"]').remove();
		}
	});
	
	//勾选"计时收费"时，显示计时收费列表和表单
	$('input[name="case[timing_fee]"]').change(function(){
		var caseTimingFeeSave=$('label#caseTimingFeeSave');
		caseTimingFeeSave.html('<input type="submit" name="submit[case_fee_timing]" value="保存" />');

		var caseFeeTimingAddForm=$('#caseFeeTimingAddForm');
		if($(this).is(':checked')){
			caseFeeTimingAddForm.show(200);
			$('[name="case_fee_timing[bill_day]"]').val('10');
			$('[name="case_fee_timing[payment_day]"]').val('20');
			$('[name="case_fee_timing[payment_cycle]"]').val('1');
			$('[name="case_fee_timing[contract_cycle]"]').val('12');
		}else{
			caseFeeTimingAddForm.hide(200);
		}
	});
	
	//审核按钮的触发
	$('button[name="submit[review]"]').click(function(){
		$(this)
		.after('<input type="submit" name="submit[send_message]" value="退回" />')
		.after('<input type="submit" name="'+$(this).attr('name')+'" value="通过" />')
		.after('<input type="text" name="review_message" />')
		.remove();
	});
	
	//小时费率页内编辑
	$('.editable').editable('misc?editable',{
		onblur:'submit',
		id:'case_lawyer-id',
		name:'hourly_fee'
	});
	
	//案下收费条件页内增加
	$('.contentTable[name="case_fee"]').children('tbody').children('tr').children('td[field="condition"]').editable(function(value,settings){
		var id=$(this).siblings('td:first').attr('id');
		
		var result;
		
		$.ajax({
			url:'case?write&case_fee_condition',
			type:'POST',
			data:{
				id:id,
				value:value
			},
			async:false,
			success:function(response){
				result=$.parseResponse(response);
			}
		});
		
		return result;
	},{
		onblur:'submit',
		data:' '
	});
	
	//响应客户来源选项
	$('select[name="case_client_extra[source_type]"]').change(function(){
		if(jQuery.inArray($(this).val(),['其他网络','媒体','老客户介绍','合作单位介绍','其他'])==-1){
			$('[name="case_client_extra[source_detail]"]').hide().attr('disabled','disabled').val('');
		}else{
			$('[name="case_client_extra[source_detail]"]').show().removeAttr('disabled');
		}
	});
	
	$('select[name="case_client_extra[classification]"]').change(function(){
		if($(this).val()=='相对方'){
			$('[name="case_client_extra[type]"]').fadeOut().attr('disable','disable');
		}else{
			$('[name="case_client_extra[type]"]').fadeIn().removeAttr('disable');
		}
		if($(this).val()!='客户'){
			$('#caseClientAddFormForClient').fadeOut().children('input').attr('disabled','disabled');
			$('#caseClientAddFormForContact').fadeIn().children('input').removeAttr('disabled');
			
		}else{
			$('#caseClientAddFormForClient').fadeIn().children('input').removeAttr('disabled');
			$('#caseClientAddFormForContact').fadeOut().children('input').attr('disabled','disabled');
		}
	});
	
	//案下律师类别为实际贡献时，增加实际贡献输入格
	$('[name="case_lawyer[role]"]').change(function(){
		if($(this).val()=='实际贡献'){
			$(this).css('width','22%')
			.siblings('[name="case_lawyer_extra[actual_contribute]"]').removeAttr('disabled').show();
		}else{
			$('[name="case_lawyer_extra[actual_contribute]"]').attr('disabled','disabled').hide();
			$(this).css('width','45%');
		}
	});
	
	$('[name="case_lawyer[role]"]').trigger('change');
});