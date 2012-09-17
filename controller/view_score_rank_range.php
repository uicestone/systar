<?php
$q_sum_3="
SELECT class,class_name,
	SUM(IF(rank_sum_3>=1 AND rank_sum_3<=50,1,0)) AS top_50,
	SUM(IF(rank_sum_3>=51 AND rank_sum_3<=100,1,0)) AS top_100,
	SUM(IF(rank_sum_3>=101 AND rank_sum_3<=200,1,0)) AS top_200,
	SUM(IF(rank_sum_3>=201 AND rank_sum_3<=300,1,0)) AS top_300,
	SUM(IF(rank_sum_3>=301 AND rank_sum_3<=400,1,0)) AS top_400,
	SUM(IF(rank_sum_3>=401,1,0)) AS top_rest
FROM `view_score` 
WHERE 1=1
";

$rangeMenu=processRange($q_sum_3,array('grade'=>'grade'));

$q_sum_3.=' GROUP BY class';

processOrderby($q_sum_3,'class');

$field=array(
	'class'=>array('title'=>'班级','td_title'=>'width=112px','content'=>'{class_name}'),
	'top_50'=>'1~50',
	'top_100'=>'51~100',
	'top_200'=>'101~200',
	'top_300'=>'201~300',
	'top_400'=>'301~400',
	'top_rest'=>'400+',
);

$menu_sum_3=array(
	'head'=>'<div class="left">'.
				'3门总分'.
			'</div>'.
			'<div class="right">'.
				$rangeMenu.
			'</div>',
);

$q_sum_5="
SELECT class,class_name,
	SUM(IF(rank_sum_5>=1 AND rank_sum_5<=50,1,0)) AS top_50,
	SUM(IF(rank_sum_5>=51 AND rank_sum_5<=100,1,0)) AS top_100,
	SUM(IF(rank_sum_5>=101 AND rank_sum_5<=200,1,0)) AS top_200,
	SUM(IF(rank_sum_5>=201 AND rank_sum_5<=300,1,0)) AS top_300,
	SUM(IF(rank_sum_5>=301 AND rank_sum_5<=400,1,0)) AS top_400,
	SUM(IF(rank_sum_5>=401,1,0)) AS top_rest
FROM `view_score` 
WHERE 1=1
";

processRange($q_sum_5,array('grade'=>'grade'));

$q_sum_5.=' GROUP BY class';

processOrderby($q_sum_5,'class',NULL,array(),false);

$menu_sum_5=array(
	'head'=>'<div style="float:left;margin-top:20px;">'.
				'5门总分'.
				$rangeMenu.
			'</div>'
);

exportTable($q_sum_3,$field,$menu_sum_3,true);

exportTable($q_sum_5,$field,$menu_sum_5,true);
?>
