<?php
$q="
	SELECT
		schedule.id AS schedule,schedule.name AS schedule_name,schedule.content AS schedule_content,schedule.experience AS schedule_experience, schedule.time_start,schedule.hours_own,schedule.hours_checked,schedule.comment AS schedule_comment,schedule.place,
		staff.name AS staff_name,staff.id AS staff
	FROM schedule LEFT JOIN staff ON staff.id = schedule.uid
	WHERE schedule.display=1 AND schedule.place<>''
";

if(got('case') && got('staff')){
	$q.=" AND schedule.`case`='".$_GET['case']."' AND uid='".$_GET['staff']."'";

}elseif(got('case')){
	$q.=" AND schedule.`case`='".$_GET['case']."'";

}elseif(got('staff')){
	$q.=" AND schedule.`uid`='".$_GET['staff']."'";

}

processOrderby($q,'time_start','DESC',array('place'));

$search_bar=processSearch($q,array('staff.name'=>'人员'));

$listLocator=processMultiPage($q);

$field=Array(
	'staff_name'=>array('title'=>'人员','content'=>'<a href="schedule?list&staff={staff}"> {staff_name}</a>','td_title'=>'width="60px"'),

	'time_start'=>array('title'=>'时间','td_title'=>'width="60px"','eval'=>true,'content'=>"
		return date('m-d H:i',{time_start});
	"),

	'place'=>array('title'=>'外出地点','td_title'=>'width="25%"')
);

$submitBar=array(
'head'=>'<div style="float:right;">'.
			$listLocator.
		'</div>'
);

exportTable($q,$field,$submitBar,true);
?>