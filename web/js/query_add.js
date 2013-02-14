$(function(){
	
	$('.item[name="client"]').on('autocompleteselect',function(event,data){
		/*有自动完成结果且已选择*/
		$(this).find('[name="client[id]"]').val(data.value).trigger('change');

		$(this).closest('.contentTable').find('[display-for~="new"]').trigger('disable');
	})
	.on('autocompleteresponse',function(){
		/*自动完成响应*/
		$(this).closest('.contentTable').find('[display-for~="new"]').trigger('enable');
		$(this).find('[name="client[id]"]').val('').trigger('change');
	});
	
	$('select[name="source[type]"]').change(function(){
		$('span#source_detail').empty();
		if(jQuery.inArray($(this).val(),['其他网络','媒体','老客户介绍','中介机构介绍','其他'])>-1){
			$('[name="source[detail]"]').removeAttr('disabled').show();
		}else{
			$('[name="source[detail]"]').hide().attr('dssabled','disabled');
		}
	}).change();
});