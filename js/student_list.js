$(function(){
	$('.editable').editable(function(value,settings){
		var id=$(this).siblings('td:first').attr('id');
		var response;
		$.ajax({
			url:'student?setclass',
			type:'POST',
			data:{id:id,value:value},
			success:function callback(result){
				console.log(result);/*for debugging uicestone 2012/8/12*/
				response=$.parseJSON(result);
				if(response.notice){
					showMessage(response.notice,'warning');
				}
			},
			async:false
		});
		if(response.num){
			$(this).siblings('td[field="num"]').html(response.num);
		}
		return response.value;
	},{
		onblur:'submit'
	});
});