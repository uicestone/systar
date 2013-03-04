$(function(){

	/*客户添加表单－客户名称自动完成事件的响应*/
	$('.item[name="client"]').on('autocompleteselect',function(event,data){
		/*有自动完成结果且已选择*/
		$(this).find('[name="case_client[client]"]').val(data.value).trigger('change');

		$(this).find('[display-for~="new"]').trigger('disable');
	})
	.on('autocompleteresponse',function(){
		/*自动完成响应*/
		$(this).find('[display-for~="new"]').trigger('enable');
		$(this).find('[name="case_client[client]"]').val('').trigger('change');
	});
	
	$('[name="client[name]"]').focus(function(){
		$(this).select();
	});

	/*案下客户类别联动*/
	$('[name="client[type]"]').on('change',function(){
		
		var addForm=$(this).parents('.add-form:first');

		if($(this).val()=='相对方'){
			addForm.find('[name="client_labels[类型]"]').hide().attr('disabled','disabled');
		}else{
			addForm.find('[name="client_labels[类型]"]').removeAttr('disabled').show();
			$(this).siblings('[name="client_labels[类型]"]').getOptionsByLabelRelative($(this).val());
		}

		if($(this).val()=='客户'){
			addForm.find('[display-for~="client"]').trigger('enable');
			addForm.find('[display-for~="non-client"]').trigger('disable');
		}else{
			addForm.find('[display-for~="client"]').trigger('disable');
			addForm.find('[display-for~="non-client"]').trigger('enable');
		}

	});

	//响应案下客户的本案地位的"其他"选项
	$('select[name="case_client[role]"]').on('change',function(){
		if($(this).val()==''){
			$(this).after('<input type="text" name="case_client[role]" placeholder="本案地位" />');
		}else{
			$('input[name="case_client[role]"]').remove();
		}
	});

	//响应客户来源选项
	$('[name="client_source[type]"]').on('change',function(){
		if($.inArray($(this).val(),['其他网络','媒体','老客户介绍','合作单位介绍','其他'])==-1){
			$('[name="client_source[detail]"]').hide().attr('disabled','disabled').val('');
		}else{
			$('[name="client_source[detail]"]').removeAttr('disabled').show();
		}
	});

	/*职员添加表单－职员名称自动完成事件的响应*/
	$('.item[name="staff"]').on('autocompleteselect',function(event,data){
		/*有自动完成结果且已选择*/
		$(this).find('[name="staff[id]"]').val(data.value).trigger('change');
	}).on('autocompleteresponse',function(){
		$(this).find('[name="staff[id]"]').val('').trigger('change');
	});

	//勾选"计时收费"时，显示计时收费列表和表单
	$('input[name="cases[timing_fee]"]').change(function(){
		var caseTimingFeeSave=$('label#caseTimingFeeSave');
		caseTimingFeeSave.html('<button type="submit" name="submit[case_fee_timing]">保存</button>');

		var caseFeeTimingAddForm=$(this).closest('.item').find('.timing-fee-detail');
		if($(this).is(':checked')){
			caseFeeTimingAddForm.show(200);
		}else{
			caseFeeTimingAddForm.hide(200);
		}
	});

	//审核按钮的触发
	$('button[name="submit[review]"]').click(function(){
		$(this)
		.after('<button type="submit" name="submit[send_message]">退回</button>')
		.after('<button type="submit" name="'+$(this).attr('name')+'">通过</button>')
		.after('<input type="text" name="review_message" />')
		.remove();
	});

	//“忽略”按钮的显示和隐藏
	$('[name^="case_fee_check"]').change(function(){
		if($('[name^="case_fee_check"]:checked').size()){
			$('[name="submit[case_fee_review]"]').removeAttr('disabled').fadeIn(200);
		}else{
			$('[name="submit[case_fee_review]"]').attr('disabled','disabled').fadeOut(200);
		}
	});

	//案下收费条件页内增加
	$('.contentTable[name="case_fee"]').children('tbody').children('tr').children('td[field="condition"]').editable(function(value,settings){
		var id=$(this).siblings('td:first').attr('id');

		var result;

		$.ajax({
			url:'/cases/write/case_fee_condition',
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

	//案下律师类别为实际贡献时，增加实际贡献输入格
	$('[name="staff[role]"]').change(function(){
		if($(this).val()=='实际贡献'){
			$(this).siblings('[name="staff_extra[actual_contribute]"]').removeAttr('disabled').show();
		}else{
			$('[name="staff_extra[actual_contribute]"]').attr('disabled','disabled').hide();
		}
	});

	//案下文件类别选择'其他'时,显示输入框
	$('[name="case_document[doctype]"]').change(function(){
		if($(this).val()=='其他'){
			$(this).css('width','7%').after('<input type="text" name="case_document[doctype_other]" style="width:8%" />')
		}else{
			$(this).css('width','15%').siblings('input[name="case_document[doctype_other]"]').remove();
		}
	});
});