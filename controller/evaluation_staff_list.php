<?php
$q="
SELECT staff.id,staff.name,position.ui_name AS position_name
FROM staff
	INNER JOIN position ON position.id=staff.position
";

processOrderby($q,'id');

$listLocator=processMultiPage($q);

$field=array(
	'name'=>array('title'=>'姓名','surround'=>array('mark'=>'a','href'=>'javascript:showWindow(\'evaluation?score&staff={id}\')')),
	'position.id'=>array('title'=>'职位','content'=>'{position_name}')
);

$menu=array(
'head'=>'<div class="right">'.
			$listLocator.
		'</div>'
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$menu,true);
?>