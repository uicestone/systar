<script type="text/javascript">
$(function(){
	var tbody=$('.contentTable').children('tbody');
	var recommendTerm=tbody.children('tr:eq(0)').children('td:eq(2)').html();
	$('#addExam').click(function(){
		var newLine=$('<tr style="visibility:visible"></tr>').prependTo(tbody);
		newLine.append('<td class="id"></td>')
		.append('<td class="name"><input type="text" name="depart" style="width:2em" />-<input type="text" name="grade_name" style="width:4em" />-<input type="text" name="name" style="width:12em" /></td>')
		.append('<td class="term"><input type="text" name="term" /></td>')
		.append('<td class="is_on"><input type="checkbox" name="is_on" value="1" checked="checked" /></td>');

		if(!tbody.children('tr:eq(1)').hasClass('oddLine')){
			newLine.addClass('oddLine');
		}

		newLine.children('.term').children('[name="term"]').val(recommendTerm);

		$('<button type="button">保存</button>').appendTo($(newLine).children('.id:eq(0)'))
		.click(function(){
			var thisLine=$(this).parent().parent();
			var post=new Object();
			post.name=thisLine.children('.name').children('[name="name"]').val();
			post.depart=thisLine.children('.name').children('[name="depart"]').val();
			post.grade_name=thisLine.children('.name').children('[name="grade_name"]').val();
			post.term=thisLine.children('.term').children('[name="term"]').val();
			post.is_on=thisLine.children('.is_on').children('[name="is_on"]').val();

			$.post('/exam/listsave?action=exam',post,function(exam){
				exam=$.parseResponse(exam);
				thisLine.children().children('input:text').remove();
				thisLine.children('.id').html(exam.id);
				thisLine.children('.name').html('<a href="/exam/paperlist/'+exam.id+'">'+exam.depart+'-'+exam.grade_name+'-'+exam.name+'</a>');
				thisLine.children('.term').html(exam.term);
			});
		});
	});
	
	$('[name="is_on"]').change(function(){
		var is_on=Number($(this).is(':checked'));
		var id=$(this).attr('id');
		$.post('exam/listsave?update=1',{table:'exam',field:'is_on',id:id,value:is_on},function(result){
			showMessage('考试'+id+'已'+(is_on?'激活':'取消激活'));
		});
	});
});
</script>
<div class="contentTableMenu">
	<button type="button" id="addExam">添加</button>
	<input type="submit" name="allocate_seat" value="排座位" title="根据当前教室设置，为已激活的考试生成座位表" />
</div>
<div class="contentTableBox">
	<?=$list?>
</div>