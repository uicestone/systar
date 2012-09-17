<?php
$q="SELECT * FROM view_score INNER JOIN view_student ON view_student.id=view_score.student WHERE 1=1";

if(!option('class') && !option('grade')){
	$manage_class=db_fetch_first("SELECT id,grade FROM class WHERE class_teacher='".$_SESSION['id']."'");
	if($manage_class){
		//将班主任的视图定位到自己班级
		option('class',$manage_class['id']);
		option('grade',$manage_class['grade']);
	}else{
		//默认显示的年级
		option('grade',$_SESSION['global']['highest_grade']);
	}
}

if(!option('exam')){
	option('exam',db_fetch_field("SELECT id FROM exam WHERE grade='".option('grade')."' ORDER BY id DESC LIMIT 1"));
}

addCondition($q,array('class'=>'view_student.class','grade'=>'view_student.grade','exam'=>'view_score.exam'),array('grade'=>'class'));

$search_bar=processSearch($q,array('view_student.name'=>'学生'));

processOrderby($q,'view_student.num');

$field=array(
	'class'=>array('title'=>'班级','td_title'=>'width=103px','content'=>'{class_name}'),
	'name'=>array('title'=>'学生','content'=>'{name}','td_title'=>'width=61px'),
	'course_1'=>array('title'=>'语文','content'=>'{course_1}<br /><span class="rank">{rank_1}</span>'),
	'course_2'=>array('title'=>'数学','content'=>'{course_2}<br /><span class="rank">{rank_2}</span>'),
	'course_3'=>array('title'=>'英语','content'=>'{course_3}<br /><span class="rank">{rank_3}</span>'),
	'course_4'=>array('title'=>'物理','content'=>'{course_4}<br /><span class="rank">{rank_4}</span>'),
	'course_5'=>array('title'=>'化学','content'=>'{course_5}<br /><span class="rank">{rank_5}</span>'),
	'course_8'=>array('title'=>'历史','content'=>'{course_8}<br /><span class="rank">{rank_8}</span>'),
	'course_7'=>array('title'=>'地理','content'=>'{course_7}<br /><span class="rank">{rank_7}</span>'),
	'course_9'=>array('title'=>'政治','content'=>'{course_9}<br /><span class="rank">{rank_9}</span>'),
	'course_10'=>array('title'=>'信息','content'=>'{course_10}<br /><span class="rank">{rank_10}</span>'),
	'course_sum_3'=>array('title'=>'3总','content'=>'{course_sum_3}<br /><span class="rank">{rank_sum_3}</span>'),
	'course_sum_5'=>array('title'=>'5总','content'=>'{course_sum_5}<br /><span class="rank">{rank_sum_5}</span>')
);

$q_avg="
SELECT *,
	ROUND(AVG(course_1),2) AS course_1,
	ROUND(AVG(course_2),2) AS course_2,
	ROUND(AVG(course_3),2) AS course_3,
	ROUND(AVG(course_4),2) AS course_4,
	ROUND(AVG(course_5),2) AS course_5,
	ROUND(AVG(course_8),2) AS course_8,
	ROUND(AVG(course_7),2) AS course_7,
	ROUND(AVG(course_9),2) AS course_9,
	ROUND(AVG(course_10),2) AS course_10,
	ROUND(AVG(course_sum_3),2) AS course_sum_3,
	ROUND(AVG(course_sum_5),2) AS course_sum_5
FROM view_score INNER JOIN view_student ON view_student.id=view_score.student
WHERE exam IN (SELECT id FROM exam WHERE is_on=1)";

addCondition($q_avg,array('class'=>'view_student.class','grade'=>'view_student.grade','exam'=>'view_score.exam'),array('grade'=>'class'));

$field_avg=array(
	'id'=>array('td_title'=>'width="204px"','content'=>'平均分'),
	'course_1'=>'',
	'course_2'=>'',
	'course_3'=>'',
	'course_4'=>'',
	'course_5'=>'',
	'course_8'=>'',
	'course_7'=>'',
	'course_9'=>'',
	'course_10'=>'',
	'course_sum_3'=>'',
	'course_sum_5'=>''
);

if(is_posted('export_to_excel')){
	$field=array(
		'class'=>array('title'=>'班级','content'=>'{class_name}'),
		'student_num'=>array('title'=>'学生','content'=>'{student_name}'),
		'course_1'=>array('title'=>'语文','content'=>'{course_1}'),
		'course_2'=>array('title'=>'数学','content'=>'{course_2}'),
		'course_3'=>array('title'=>'英语','content'=>'{course_3}'),
		'course_4'=>array('title'=>'物理','content'=>'{course_4}'),
		'course_5'=>array('title'=>'化学','content'=>'{course_5}'),
		'course_8'=>array('title'=>'历史','content'=>'{course_8}'),
		'course_7'=>array('title'=>'政治','content'=>'{course_7}'),
		'course_9'=>array('title'=>'地理','content'=>'{course_9}'),
		'course_10'=>array('title'=>'信息','content'=>'{course_10}'),
		'course_sum_3'=>array('title'=>'3总','content'=>'{course_sum_3}'),
		'course_sum_5'=>array('title'=>'5总','content'=>'{course_sum_5}')
	);
}else{
	$listLocator=processMultipage($q);
}

$table=fetchTableArray($q,$field);

if(is_posted('export_to_excel')){
	model('document');
	document_exportHead('成绩.xls');
	arrayExportExcel($table);
	exit;

}else{
	$menu=array(
	'head'=>'<div class="left">'.
				'<button type="button" onclick="post(\'updateScore\',true)">更新</button>'.
				'<button type="button" onclick="post(\'export_to_excel\',true)" disabled="disabled" title="本功能将于近期开放">导出</button>'.
			'</div>'.
			'<div class="right">'.
				$listLocator.
			'</div>'
	);
}
?>
