<?php
model('user');

if(is_posted('submit')){
	$submitable=true;
	
	$_SESSION[IN_UICE]['post']=array_replace_recursive($_SESSION[IN_UICE]['post'],$_POST);
	
	if(post('student_comment/reply_to',user_check(post('student_comment_extra/reply_to_username')))<0){
		$submitable=false;
	}
	
	if(is_logged('parent')){
		$student_id=student_getIdByParentUid($_SESSION['id']);
	}else{
		$student_id=student_getIdByParentUid(post('student_comment/reply_to'));
	}
	
	if($submitable){
		student_addComment($student_id,post('student_comment'));
		unset($_SESSION[IN_UICE]['post']['student_comment']);
		unset($_SESSION[IN_UICE]['post']['student_comment_extra']);
	}
}

$q="
SELECT student_comment.title,student_comment.content,FROM_UNIXTIME(student_comment.time,'%Y-%m-%d') AS date,student_comment.username,student_comment.student,
	view_student.name AS student_name
FROM student_comment INNER JOIN view_student ON student_comment.student=view_student.id
WHERE student_comment.reply_to='".$_SESSION['id']."' 
	OR student_comment.uid='".$_SESSION['id']."' 
	OR (
		'".isset($_SESSION['manage_class'])."' 
		AND view_student.class='".$_SESSION['manage_class']['id']."'
	)
ORDER BY time DESC
";

$list_locator=processMultipage($q);

$field=array(
	'date'=>array('title'=>'日期','td_title'=>'width="100px"'),
	'username'=>array('title'=>'用户','td_title'=>'width="120px"'),
	'student_name'=>array('title'=>'学生','td_title'=>'width="60px"','surround'=>array('mark'=>'a','href'=>'student?edit={student}')),
	'title'=>array('title'=>'标题','td_title'=>'width="120px"','td'=>'class="ellipsis" title="{title}"'),
	'content'=>array('title'=>'内容','td'=>'class="ellipsis" title="{content}"')
);

$menu=array(
	'head'=>'<div class="right">'.$list_locator.'</div>',
	'foot'=>template('student_interactive_send')
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$menu);
?>