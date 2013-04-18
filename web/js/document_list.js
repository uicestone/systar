$(function () {
	
	var section = aside.children('section[for="'+hash+'"]');
	
	$(document).on('drop dragover', function(e){
		e.preventDefault();
	});
	
	section.find('#fileupload').fileupload({
        dataType: 'json',
        done: function (event, data) {
			var uploadItem=section.children('.upload-list-item:first').clone();
			
			uploadItem.appendTo(section).removeClass('hidden')
				.attr('id',data.result.data.id).children('[name="document[name]"]').val(data.result.data.name);

			uploadItem.find('select').each(function(index,object){
				var options={};
				options.allowClear=true;
				options.formatNoMatches=function(term){
					var that=this;
					this.element.data().select2.results.off('.addnewoption').on('click.addnewoption',function(){
						that.element.append($('<option/>',{text:term,value:term,selected:'selected'})).trigger('change');
					});
					return '添加新标签：'+term;
				};

				$(object).select2(options);
			});
			
			uploadItem.children(':input').on('change',function(){
				var data = $(this).serialize();
				$.post('/document/update/'+uploadItem.attr('id'),data);
			});
	
        },
		dropZone:section
    });
});