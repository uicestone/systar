$(function(){
	
	$('.item[name="client[name]"]').on('autocompleteselect',function(event,data){
		/*有自动完成结果且已选择*/
		console.log('select');
		$(this).find('[name="client[id]"]').val(data.value).trigger('change');

		$(this).closest('.contentTableBox').find('[display-for~="new"]').trigger('disable');
	})
	.on('autocompleteresponse',function(){
		/*自动完成响应*/
		$(this).closest('section').find('[display-for~="new"]').trigger('enable');
		$(this).find('[name="client[id]"]').val('').trigger('change');
	});
	
	$('select[name="client_profiles[来源类型]"]').change(function(){
		$('span#source_detail').empty();
		if(jQuery.inArray($(this).val(),['其他网络','媒体','老客户介绍','中介机构介绍','其他'])>-1){
			$('[name="client_profiles[来源]"]').removeAttr('disabled').show();
		}else{
			$('[name="client_profiles[来源]"]').hide().attr('dssabled','disabled');
		}
	}).change();
});