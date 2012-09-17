<?php
if(is_posted('allocate_seat')){
	exam_allocate_seat();
}

$q="SELECT 
		exam.id AS id,exam.name AS name,exam.term AS term,exam.is_on,exam.seat_allocated,exam.depart,
		grade.name AS grade_name
	FROM exam INNER JOIN grade ON exam.grade=grade.id
	WHERE
		1=1
	";
		
processOrderby($q,'exam.id','DESC',array('exam.name'));

$listLocator=processMultiPage($q);

$field=array(
	'id'=>array('title'=>'编号','td_title'=>'width="70px"'),
	'name'=>array('title'=>'考试名称','eval'=>true,'content'=>"
		return '<a href=\"exam.php?exam={id}\" style=\"float:left;\">{depart}-{grade_name}-{name}</a>'.({seat_allocated}?' <a href=\"exam.php?exam={id}&view_seat\" style=\"float:right;\">座位表</a>':'');
	"),
	'term'=>array('title'=>'学期'),
	'is_on'=>array('title'=>'激活','eval'=>true,'content'=>"
		return '<input type=\"checkbox\" name=\"is_on\" id=\"{id}\" '.({is_on}?'checked=\"checked\"':'').' />';
	")
);

$submitBar=array(
	'head'=>'<div style="float:left;">'.
				'<button type="button" id="addExam">添加</button>'.
				'<input type="submit" name="allocate_seat" value="排座位" title="根据当前教室设置，为已激活的考试生成座位表" />'.
			'</div>'.
			'<div style="float:right;">'.
				$listLocator.
			'</div>'
);
exportTable($q,$field,$submitBar,true);
?>