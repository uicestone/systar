<script type="text/javascript">
$(function(){
	var tbody=$('.contentTable').children('tbody');

	$('#addExamPaper').click(function(){
		var newLine=$('<tr style="visibility:visible"><td class="id"></td><td class="course_name"><input type="text" name="course_name" /></td><td class="students">&nbsp;</td><td class="teacher_group_name">&nbsp</td><td class="is_extra_course"><input type="checkbox" name="is_extra_course" value="1" /></td><td class="is_scoring"><input type="checkbox" name="is_scoring" value="1" checked="checked" /></td></tr>').prependTo(tbody);

		if(!tbody.children('tr:eq(1)').hasClass('oddLine')){
			newLine.addClass('oddLine');
		}

		$('<button type="button">保存</button>').appendTo($(newLine).children('.id:eq(0)'))
		.click(function(){
			var thisLine=$(this).parent().parent();
			var post=new Object();
			post.course_name=thisLine.children('.course_name').children('[name="course_name"]').val();
			post.is_extra_course=thisLine.children('.is_extra_course').children('[name="is_extra_course"]:checked').val();
			post.is_scoring=thisLine.children('.is_scoring').children('[name="is_scoring"]').val();
			post.exam=<?php echo intval($this->input->get('exam'));?>;

			$.post('exam.php?save&action=exam_paper',post,function(line){
				line=$.parseResponse(line);
				thisLine.children().children('input:text').remove();
				thisLine.children('.course_name').html(line.course_name);
				thisLine.children('.id').html(line.id);
				thisLine.children('.students').html(line.students);
				thisLine.children('.teacher_group_name').html(line.teacher_group_name);
				thisLine.children('.is_extra_course').children('input').attr('disabled','disabled');
			});
		});
	});
	
	$('[name="is_scoring"]').change(function(){
		var is_scoring=Number($(this).is(':checked'));
		var id=$(this).attr('id');
		$.post('exam.php?save&update',{table:'exam_paper',field:'is_scoring',id:id,value:is_scoring},function(result){
			console.log(result);
			showMessage('考试'+id+'已'+(is_scoring?'开启阅卷':'关闭阅卷'));
		});
	});
});
</script>