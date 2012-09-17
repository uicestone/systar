<?php
if(is_posted('grade')){
	option('grade',$_POST['grade']);
}

$q="
	SELECT class.id, class.name, grade.name AS grade_name, depart, course.name AS extra_course_name,
		staff.name AS class_teacher_name
	FROM class INNER JOIN grade ON class.grade = grade.id
		LEFT JOIN course ON course.id = class.extra_course
		LEFT JOIN staff ON staff.id = class.class_teacher
	WHERE grade>='".$_SESSION['global']['highest_grade']."'
";

addCondition($q,array('grade'=>'class.grade'));
		
$search_bar=processSearch($q,array('name'=>'班级','depart'=>'部门'));

processOrderby($q,'class.id','ASC');

$listLocator=processMultiPage($q);

$field=array(
	'class.id'=>array('title'=>'名称','td'=>'title="编号：{id}"','content'=>'{name}','surround'=>array('mark'=>'a','href'=>'class?edit={id}')),
	'depart'=>array('title'=>'部门'),
	'extra_course_name'=>array('title'=>'加一'),
	'class_teacher_name'=>array('title'=>'班主任')
);

$submitBar=array(
	'head'=>'<div class="right">'.
				$listLocator.
			'</div>'
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$submitBar,true);
?>