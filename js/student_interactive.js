$(function(){
	$('.contentTable>tbody>tr').mouseenter(function(){
		var parent_name=$(this).children('td[field="username"]').html();
		$('<a href="#" class="reply right">回复</a>').prependTo($(this).children('td[field="username"]')).click(function(){
			$('[name="student_comment_extra[reply_to_username]"]').val(parent_name);
			$('[name="student_comment[title]"]').focus();
		});
	}).mouseleave(function(){
		$(this).find('.reply').remove();
	});
});