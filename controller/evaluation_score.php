<?php
$staff=intval($_GET['staff']);

$position=db_fetch_field("SELECT position FROM staff WHERE id='".$staff."'");

$q="
SELECT evaluation_indicator.id,evaluation_indicator.name,evaluation_indicator.weight,
	evaluation_score.id AS score_id,evaluation_score.score,evaluation_score.comment		#,evaluation_score.anonymous
FROM evaluation_indicator 
	LEFT JOIN evaluation_score ON (
		evaluation_indicator.id=evaluation_score.indicator 
		AND staff='".$staff."' 
		AND uid='".$_SESSION['id']."'
	)
WHERE critic='".$_SESSION['position']."'
	AND position='".$position."'
";

processOrderby($q,'id');

$listLocator=processMultiPage($q);

$field=array(
	'name'=>array('title'=>'考核指标','td'=>'id="{id}"','content'=>'{name}({weight})','td_title'=>'width="20%"'),
	/*'anonymous'=>array('title'=>'匿名','td_title'=>'width=55px"','td'=>'style="text-align:center"','eval'=>true,'content'=>"
		return '<input type=\"checkbox\" value=\"1\" '.(!'{score_id}' || '{anonymous}'?'checked=\"checked\"':'').' />';
	"),*/
	'score'=>array('title'=>'分数','td_title'=>'width="70px"','eval'=>true,'content'=>"
		if('{score}'==0){
			return '<input type=\"text\" style=\"width:50px;\" />';
		}else{
			return '<span>{score}</span>';
		}
	"),
	'comment'=>array('title'=>'附言','eval'=>true,'content'=>"
		if(!'{comment}'){
			return '<input type=\"text\" />';
		}else{
			return '<span>{comment}</span>';
		}
	")
);

$menu=array(
'head'=>'<div class="right">'.
			$listLocator.
		'</div>'
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$menu,true);
?>