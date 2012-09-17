$(function(){
	var typeRadio = $('input[name="type"]');
	var caseSelect = $('select[name="schedule[case]"]');

	typeRadio.change(function(){

		var type=$(this).val();//日程类别，0案件，1所务，2营销

		//根据日程类别获得案件列表
		caseSelect.getOptions('case_getListByScheduleType',type,1,function(scheduleCase){
			caseSelect.trigger('change',{scheduleCase:scheduleCase,type:type});
		});
		
	});

	//监听案件变化
	caseSelect.change(function(event,data){
		if(!data){
			scheduleCase = $(this).val();
		}else{
			scheduleCase = data.scheduleCase;
		}
		
		if((data && data.type==2) || $('input[name="type"]:checked').val()==2){
			if(scheduleCase==11){
				$('select[name="schedule[client]"]').hide(200).attr('disabled','disabled');
				$('input[name="schedule[client]"]').show(200).removeAttr('disabled');
				
			}else{
				$('input[name="schedule[client]"]').hide(200).attr('disabled','disabled');
				$('select[name="schedule[client]"]').show(200).removeAttr('disabled')
				.getOptions('client_getListByCase',scheduleCase,1);
			}
		}else{
			$('[name="schedule[client]"]').hide(200).attr('disabled','disabled');
		}
	});

	$( "#combobox" ).combobox();
	$( "#toggle" ).click(function() {
		$( "#combobox" ).toggle();
	});
});