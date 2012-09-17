$(document).ready(function(){
	//触发响应
	$('select[name="source[type]"]').change(function(){
		$('span#source_detail').empty();
		if(jQuery.inArray($(this).val(),['其他网络','媒体','老客户介绍','中介机构介绍','其他'])>-1){
			$('span#source_detail').html('<input type="text" name="source[detail]" style="width:49%" />');
		}
	});
});