<?php
$evaluation_result=array(
	'_field'=>array(
		'互评分',
		'自评分',
		'主管分'
	),
	array(
		evaluation_getPeer(),
		evaluation_getSelf(),
		evaluation_getManager()
	)
);

$q="
SELECT evaluation_indicator.name,evaluation_indicator.weight,
	evaluation_score.comment,
	position.ui_name AS position_name,
	staff.name AS staff_name
FROM evaluation_score 
	INNER JOIN evaluation_indicator ON evaluation_indicator.id=evaluation_score.indicator AND evaluation_score.quarter='".$_G['quarter']."'
	INNER JOIN staff ON staff.id=evaluation_score.uid
	INNER JOIN position ON evaluation_indicator.critic=position.id
WHERE comment IS NOT NULL AND staff='".$_SESSION['id']."'
";

processOrderby($q,'evaluation_score.time','DESC');

$listLocator=processMultiPage($q);

$field=array(
	'name'=>array('title'=>'评分项','content'=>'{name}({weight})'),
	'comment'=>array('title'=>'附言'),
	'staff_name'=>array('title'=>'评分人','content'=>'{staff_name}({position_name})')
);

$menu=array(
	'head'=>'<div class="right">'.
				$listLocator.
			'</div>'
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field);
?>