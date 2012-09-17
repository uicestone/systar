<?php
if(date('w')==1){//今天是星期一
	$last_week_monday=strtotime("-1 Week Monday");
}else{
	$last_week_monday=strtotime("-2 Week Monday");
}

$q_staffly_workhours="
SELECT staff.name AS staff_name,lastweek.hours AS lastweek,last2week.hours AS last2week
FROM staff INNER JOIN (
	SELECT uid,SUM(schedule.hours_own) AS hours
	FROM schedule
	WHERE completed=1 AND schedule.time_start >= '".$last_week_monday."' AND schedule.time_start < '".($last_week_monday+86400*7)."'
	GROUP BY uid
)lastweek ON staff.id=lastweek.uid
INNER JOIN (
	SELECT uid,SUM(schedule.hours_own) AS hours
	FROM schedule
	WHERE completed=1 AND schedule.time_start >= '".($last_week_monday-86400*7)."' AND schedule.time_start < '".$last_week_monday."'
	GROUP BY uid
)last2week ON staff.id=last2week.uid
ORDER BY lastweek DESC"
;

$staffly_workhours=db_toArray($q_staffly_workhours);
$chart_staffly_workhours_catogary=json_encode(array_sub($staffly_workhours,'staff_name'));
$chart_staffly_workhours_series=array(
	array('name'=>'上上周','data'=>array_sub($staffly_workhours,'last2week')),
	array('name'=>'上周','data'=>array_sub($staffly_workhours,'lastweek'))
);
$chart_staffly_workhours_series=json_encode($chart_staffly_workhours_series,JSON_NUMERIC_CHECK);
?>