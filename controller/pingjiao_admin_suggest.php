<?php
$q="SELECT *
	 FROM result,student_class,class
	 WHERE teacher='".$_GET['teacher']."'
	 	AND suggest <> ''
	 	AND result.student=student_class.student
		AND student_class.class=class.id
		AND result.term=student_class.term
		AND result.term='".$_SESSION['global']['current_term']."'";

processRange($q,array('class'=>'class.id','grade'=>'class.grade','term'=>'result.term'));

$field=array(
	'suggest'=>array('title'=>'学生意见和建议')
);

exportTable($field,$q);