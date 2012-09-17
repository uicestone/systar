<?php
$q="
	SELECT * FROM news WHERE display=1 AND company='".$_G['company']."'
";

processOrderby($q,'time','DESC');

$listLocator=processMultiPage($q);

$field=array(
	'time'=>array('title'=>'日期','td_title'=>'width="80px"','eval'=>true,'content'=>"
		return date('m-d',{time});
	"),
	'title'=>array('title'=>'标题','content'=>'<a href="javascript:showWindow(\'news?edit={id}\')">{title}</a>'),
	'username'=>array('title'=>'发布人')
);

$submitBar=array(
'head'=>'<div style="float:right;">'.
			$listLocator.
		'</div>'
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$submitBar);
?>