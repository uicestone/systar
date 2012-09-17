<?php
if(is_posted('schedule_list_comment')){
	foreach($_POST['schedule_list_comment'] as $id => $comment){
		$schedule_list_comment_return=schedule_set_comment($id,$comment);
		
		echo $schedule_list_comment_return['comment'];
		
		sendMessage($schedule_list_comment_return['uid'],

		$schedule_list_comment_return['comment'].'（日志：'.$schedule_list_comment_return['name'].'收到的点评）',
		'你的日志："'.$schedule_list_comment_return['name'].'"收到点评');
	}
}

if(is_posted('schedule_list_hours_checked') || is_posted('schedule_list_hours_checked')){
	foreach($_POST['schedule_list_hours_checked'] as $id => $hours_checked){
		echo schedule_check_hours($id,$hours_checked);
	}
}
?>