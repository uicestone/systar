<?php
$exam=intval($_GET['exam']);

$q="SELECT 
		view_student.num,view_student.class_name,view_student.name AS student_name,
		exam_student.room,exam_student.seat,course.name AS course_name
	FROM exam_student INNER JOIN view_student ON exam_student.student=view_student.id
		LEFT JOIN course ON exam_student.extra_course=course.id
	WHERE
		exam_student.exam='".$exam."'
";
		
processOrderby($q,'view_student.num','ASC');

$listLocator=processMultiPage($q);

$field=array(
	'num'=>array('title'=>'学号'),
	'student_name'=>array('title'=>'姓名'),
	'room'=>array('title'=>'教室'),
	'seat'=>array('title'=>'座位'),
	'course_name'=>array('title'=>'加科')
);

$submitBar=array(
	'head'=>'<div style="float:left;">'.
				'<button type="button" onclick="location.href=\'exam.php\'">返回</button>'.
			'</div>'.
			'<div style="float:right;">'.
				$listLocator.
			'</div>'
);

require 'view/exam_list.htm';
exportTable($q,$field,$submitBar,true);
?>