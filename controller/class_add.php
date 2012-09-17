<?php
model('staff');

getPostData();

$submitable=false;

if(is_posted('submit')){
	$submitable=true;

	$_SESSION[IN_UICE]['post']=array_replace_recursive($_SESSION[IN_UICE]['post'],$_POST);
	
	if(post('class/class_teacher',staff_check(post('class_extra/class_teacher_name')))<0){
		$submitable=false;
	}

	processSubmit($submitable);
}

post('class/class_teacher')>0 && post('class_extra/class_teacher_name',db_fetch_field("
	SELECT name 
	FROM staff 
	WHERE id=(SELECT class_teacher FROM class WHERE id='".post('class/id')."')
"));

$q_class_leadership="
	SELECT student.name,student_class.position 
	FROM student INNER JOIN student_class 
		ON (student.id=student_class.student AND student_class.term='".$_SESSION['global']['current_term']."')
	WHERE student_class.class='".post('class/id')."'
		AND student_class.position IS NOT NULL
";
$field_class_leadership=array(
	'student.name'=>array('title'=>'学生'),
	'student_class.position'=>array('title'=>'职务')
);
?>