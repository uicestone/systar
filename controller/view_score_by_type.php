<?php
$q="
SELECT
	type,
	count(*) AS amount,
	round(avg(course_1),2) AS course_1,
	round(avg(course_2),2) AS course_2,
	round(avg(course_3),2) AS course_3,
	round(avg(course_4),2) AS course_4,
	round(avg(course_5),2) AS course_5,
	round(avg(course_8),2) AS course_8,
	round(avg(course_7),2) AS course_7,
	round(avg(course_sum_3),2) AS course_sum_3,
	round(avg(course_sum_5),2) AS course_sum_5  
FROM view_score INNER JOIN view_student ON view_student.id=view_score.student
WHERE 1=1";

$rangeMenu=processRange($q,array('grade'=>'view_student.grade'));

$q.=' GROUP BY view_student.type';

processOrderby($q,'student_num');

$field=array(
	'type'=>array('title'=>'分类','td_title'=>'width=112px'),
	'amount'=>'人数',
	'course_1'=>'语文',
	'course_2'=>'数学',
	'course_3'=>'英语',
	'course_4'=>'物理',
	'course_5'=>'化学',
	'course_8'=>'历史',
	'course_7'=>'政治',
	'course_sum_3'=>'3总',
	'course_sum_5'=>'5总'
);

$menu=array(
	'head'=>'<div class="right">'.
				$rangeMenu.
			'</div>'
);

exportTable($q,$field,$menu,true);
?>
