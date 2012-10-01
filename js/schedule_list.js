$(document).ready(function(){
	
	//评语保存
	$('textarea[name^="schedule_list_comment"]').change(function(){
		var current_schedule_list_comment=$(this);
		$.post('schedule?listwrite',$(this).serialize(),function(result){
			showMessage('评语已保存');
			current_schedule_list_comment.val(result);
		});
	});

	$('.editable').editable('misc?editable',{
		onblur:'submit',
		id:'schedule-id',
		name:'hours_checked',
		callback:function(value,settings){
			$(this).removeClass('hours_own').addClass('hours_checked');
		}
	});
	
	//相应全选按钮
	$('input[name="schedule_checkall"]').change(function(){
		if($(this).is(':checked')){
			$('input[name^="schedule_check"]').attr('checked','checked');
		}else{
			$('input[name^="schedule_check"]').removeAttr('checked');
		}
	});
	
});