$(function(){
	$('[name="contact[classification]"]').change(function(){
		$('[name="contact[type]"]').getOptions('client',$(this).val());
	});
	
	$('#contactRelatedAdd,#contactContactAdd').click(function(){
		//响应每一栏标题上的"+"并显示/隐藏添加菜单
		var form=$('#'+$(this).attr('id')+'Form');
		if(form.is(':hidden')){
			form.show(200);
			$(this).html('-');
		}else{
			form.hide(200);
			$(this).html('+');
		}
	});
});