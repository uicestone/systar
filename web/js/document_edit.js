$(function () {
	var section = aside.children('section[hash="'+hash+'"]');
	
	section.find('select.chosen[name="read_mod_people"]').on('change',function(event,newLabel){

		var id=page.children('section[hash="'+hash+'"]').children('form').attr('id');
		var people,method;console.log(id);

		if(newLabel){
			people=newLabel;
			method='add';
		}else if(event.added && id){
			people=event.added.id;
			method='add';
		}else if(event.removed && id){
			people=event.removed.id;
			method='remove';
		}

		if(method && id){
			$.post('/document/'+method+'mod/'+id+'/'+people+'/1');
		}

	});
		
});
