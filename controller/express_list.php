<?php
$q="
	SELECT 
		express.id,express.destination,express.content,express.comment,express.time_send,express.num,
		staff.name AS sender_name
	FROM express LEFT JOIN staff ON staff.id=express.sender
	WHERE express.display=1
";

$search_bar=processSearch($q,array('num'=>'单号','staff.name'=>'寄送人','destination'=>'寄送地点'));

processOrderby($q,'time_send','DESC');

$listLocator=processMultiPage($q);

$field=array(
	'content'=>array('title'=>'寄送内容','surround'=>array('mark'=>'a','href'=>'express?edit={id}'),'td'=>'class="ellipsis" title="{content}"'),
	'time_send'=>array('title'=>'日期','td_title'=>'width="60px"','eval'=>true,'content'=>"
		return date('m-d',{time_send});
	"),
	'sender_name'=>array('title'=>'寄送人'),
	'destination'=>array('title'=>'寄送地点','td'=>'class="ellipsis" title="{destination}"'),
	'num'=>array('title'=>'单号'),
	'comment'=>array('title'=>'备注')
);

$submitBar=array(
'head'=>'<div class="right">'.
			$listLocator.
		'</div>'
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$submitBar);
?>