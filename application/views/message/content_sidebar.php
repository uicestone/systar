<table class="contentTable search-bar">
	<thead><tr><th width="80px">发送消息</td></tr></thead>
	<tbody>
		<tr>
			<td>
				<textarea name="content" placeholder="内容"></textarea>
			</td>
		</tr>
		<tr>
			<td>
				<input id="fileupload" type="file" name="document" data-url="/document/submit" multiple="multiple" />
				<div id="upload-info"></div>
			</td>
		</tr>
		<tr>
			<td class="submit">
				<button type="submit" name="send">发送</button>
			</td>
		</tr>
	</tbody>
</table>
<p class="upload-list-item hidden">
	<input type="hidden" name="documents[]" disabled="disabled" />
	<input type="text" name="document[name]" disabled="disabled" placeholder="名称" />
	<hr />
</p>
<script type="text/javascript">
$(function () {
	
	var section = aside.children('section[for="'+hash+'"]');
	
	section.find('#fileupload').fileupload({
        dataType: 'json',
        done: function (event, data) {
			
			$(document).setBlock(data.result);
			
			var uploadItem=section.children('.upload-list-item:first').clone();
			
			uploadItem.appendTo(section.find('#upload-info'))
				.removeClass('hidden')
				.attr('id',data.result.data.id)
					.children('[name="document[name]"]')
					.removeAttr('disabled')
					.val(data.result.data.name)
				.end()
					.children('[name="documents[]"]')
					.removeAttr('disabled')
					.val(data.result.data.id);

			uploadItem.children('[name="document[name]"]').on('change',function(){
				var data = $(this).serialize();
				$.post('/document/update/'+uploadItem.attr('id'),data);
			});
	
        },
		dropZone:section
    });
});
</script>
