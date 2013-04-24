$(function () {
	
	var section = aside.children('section[for="'+hash+'"]');
	
	section.find('#save').on('click',function(event){
		event.stopImmediatePropagation();
		$.refresh(hash);
	});

	section.find('#fileupload').fileupload({
        dataType: 'json',
        done: function (event, data) {
			
			$(document).setBlock(data.result);
			
			section.find('#save').show();
			
			var uploadItem=section.children('.upload-list-item:first').clone();
			
			uploadItem.appendTo(section.find('#upload-info')).removeClass('hidden')
				.attr('id',data.result.data.id).children('[name="document[name]"]').val(data.result.data.name);

			uploadItem.find('select').each(function(index,object){
			var options={
				dropdownCss:{minWidth:100},
				createSearchChoice:function(term,results){
					if(typeof results==='undefined'){
						return {id:term,text:term,create:true};
					}

					var options=[];
					$.each(results,function(){
						options.push(this.text);
					});

					if($.inArray(term,options)===-1){
						return {id:term,text:term,create:true};
					}
				},
				formatSelection:function(object,container){
					if(this.element.find('option[value="'+object.id+'"]').length===0){
						this.element.append($('<option/>',{value:object.id,text:object.text}));

						if(this.element.is('[multiple]')){
							var val=this.element.val();
							val.push(object.id);
							this.element.val(val);
						}
						else{
							this.element.val(object.id);
						}

					}
					return object.text;
				},
				formatResult:function(object,container,query){
					if(object.create){
						return '添加：'+object.text;
					}
					else if(object.text){
						return object.text;
					}
					else{
						return object.id;
					}
				}
			};
			
			$(object).select2(options)
			});
			
			uploadItem.children('[name="document[name]"]').on('change',function(){
				var data = $(this).serialize();
				$.post('/document/update/'+uploadItem.attr('id'),data);
			});
	
        },
		dropZone:section
    });
});
