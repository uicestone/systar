$(function(){
	$('[field="score"],[field="comment"]').children('input').change(function(){
		var input=$(this);
		var cell=input.parent('td');

		var uriSegments=window.location.pathname.substr(1).split('/');
		
		$.post('/evaluation/scorewrite/'+uriSegments[2],{
			indicator:cell.siblings('td:first').attr('id'),
			field:cell.attr('field'),
			value:input.val(),
			anonymous:Number(cell.siblings('[field="anonymous"]').children('input').is(':checked'))
		},function(response){
			response=$.parseResponse(response);
			if(typeof response !== 'undefined'){
				input.after('<span>'+response+'</span>').remove();
			}
		});
	});
	
	/*$('[field="anonymous"]').children('input').change(function(){
		$.post(changeURLPar(unsetURLPar(location.href,'score'),'score_write',1),{
			indicator:$(this).parent('td').siblings('td:first').attr('id'),
			anonymous:Number($(this).is(':checked'))
		},function(response){
			$.parseResponse(response);
		});
	});*/
});