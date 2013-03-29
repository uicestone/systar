<form>
	<table class="contentTable search-bar" cellpadding="0" cellspacing="0" align="center">
		<thead><tr><th width="80px">搜索</td></tr></thead>
		<tbody>
			<tr>
				<td><input type="text" name="name" value="<?=option('search/name')?>" placeholder="名称" title="名称" /></td>
			</tr>
			<tr>
				<td>
					<select name="labels[]" data-placeholder="标签" multiple="multiple"><?=options($this->document->getAllLabels(),option('search/labels'))?></select>
				</td>
			</tr>
			<tr>
				<td class="submit">
					<button type="submit" name="search" tabindex="0">搜索</button>
					<button type="submit" name="search_cancel" tabindex="1"<?if(is_null(option('search/name')) && !option('search/labels')){?> class="hidden"<?}?>>取消</button>
				</td>
			</tr>
		</tbody>
	</table>
</form>
<input id="fileupload" type="file" name="document" data-url="/document/submit" multiple="multiple" />
<p class="upload-list-item hidden"><span class="filename"></span>
	<input type="text" name="document[name]" placeholder="名称" />
	<select name="labels[]" data-placeholder="标签" multiple="multiple" class="view">
		<?=options($this->document->getAllLabels(), array_dir('_SESSION/document/index/search/labels'))?>
	</select>
	<hr/>
</p>
<script>
$(function () {
	
	var section = aside.children('section[for="document"]');
	
	$(document).on('drop dragover', function(e){
		e.preventDefault();
	});
	
	$('#fileupload').fileupload({
        dataType: 'json',
        done: function (event, data) {
			var uploadItem=section.children('.upload-list-item:first').clone();
			
			uploadItem.appendTo(section).removeClass('hidden')
				.attr('id',data.result.data.id).children('.filename').text(data.result.data.name);

			uploadItem.find('select').each(function(index,element){
				$(element).chosen({search_contains:true,allow_single_deselect:true,no_results_text:'添加新标签',no_results_callback:function(term){
					$(element).append('<option value="'+term+'" selected="selected">'+term+'</option>').trigger('liszt:updated').trigger('change');
				}});
			});
			
			uploadItem.on('liszt:showing_dropdown',function(){
				console.log('123');
			});

			uploadItem.children(':input').on('change',function(){
				var data = $(this).serialize();
				$.post('/document/update/'+uploadItem.attr('id'),data);
			});
	
        },
		dropZone:aside.children('section[for="document"]')
    });
});
</script>