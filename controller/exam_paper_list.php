<?php
post('exam/id',intval($_GET['exam']));

$q="SELECT 
		course.id AS course,course.name AS course_name,
		exam_paper.id AS id,exam_paper.is_extra_course,exam_paper.students,exam_paper.is_scoring,exam_paper.term,
		grade.name,
		staff_group.name AS teacher_group_name
	FROM exam_paper
		INNER JOIN course ON course.id=exam_paper.course
		INNER JOIN exam ON exam_paper.exam=exam.id
		INNER JOIN grade ON grade.id=exam.grade
		LEFT JOIN staff_group ON staff_group.id=exam_paper.teacher_group
	WHERE
		exam.id='".post('exam/id')."'
	";
		
processOrderby($q,'course.id');

$listLocator=processMultiPage($q);

$field=array(
	'id'=>array('title'=>'编号','td_title'=>'width="70px"'),
	'course'=>array('title'=>'学科','content'=>'{course_name}'),
	'students'=>array('title'=>'考试人数'),
	'teacher_group_name'=>array('title'=>'备课组'),
	'is_extra_course'=>array('title'=>'分科考试','eval'=>true,'content'=>"
		return '<input type=\"checkbox\" '.({is_extra_course}?'checked=\"checked\"':'').' disabled=\"disabled\" />';
	"),
	'is_scoring'=>array('title'=>'开启登分','eval'=>true,'content'=>"
		return '<input type=\"checkbox\" name=\"is_scoring\" id=\"{id}\" '.({is_scoring}?'checked=\"checked\"':'').' />';
	")
);

$submitBar=array(
	'head'=>'<div class="left">'.
				'<button type="button" id="addExamPaper">添加</button>'.
				'<button type="button" onclick="redirectPara(this,\'exam\')">返回</button>'.
			'</div>'.
			'<div style="float:right;">'.
				$listLocator.
			'</div>'
);
exportTable($q,$field,$submitBar,true);
?>