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

if(date('w')==1){//今天是星期一
	$start_of_this_week=strtotime($_G['date']);
}else{
	$start_of_this_week=strtotime("-1 Week Monday");
}
$start_of_this_month=strtotime(date('Y-m',$_G['timestamp']).'-1');
$start_of_this_year=strtotime(date('Y',$_G['timestamp']).'-1-1');

$days_passed_this_week=ceil(($_G['timestamp']-$start_of_this_week)/86400);
$days_passed_this_month=ceil(($_G['timestamp']-$start_of_this_month)/86400);
$days_passed_this_year=ceil(($_G['timestamp']-$start_of_this_year)/86400);

$q="
	SELECT staff.name aS staff_name,
		this_week.sum AS this_week_sum,ROUND(this_week.avg,2) AS this_week_avg,
		this_month.sum AS this_month_sum,ROUND(this_month.avg,2) AS this_month_avg,
		this_year.sum AS this_year_sum,ROUND(this_year.avg,2) AS this_year_avg
	FROM
	(
		SELECT uid,SUM(hours_own) AS sum, SUM(hours_own)/".$days_passed_this_week." AS avg
		FROM schedule 
		WHERE time_start>='".$start_of_this_week."' AND time_start<'".$_G['timestamp']."' 
			AND completed=1 AND display=1
		GROUP BY uid
	)this_week
	INNER JOIN
	(
		SELECT uid,SUM(hours_own) AS sum, SUM(hours_own)/".$days_passed_this_month." AS avg
		FROM schedule 
		WHERE time_start>='".$start_of_this_month."' AND time_start<'".$_G['timestamp']."' 
			AND completed=1 AND display=1
		GROUP BY uid
	)this_month USING(uid)
	INNER JOIN
	(
		SELECT uid,SUM(hours_own) AS sum, SUM(hours_own)/".$days_passed_this_year." AS avg
		FROM schedule 
		WHERE time_start>='".$start_of_this_year."' AND time_start<'".$_G['timestamp']."' 
			AND completed=1 AND display=1
		GROUP BY uid
	)this_year USING(uid)
	INNER JOIN staff ON staff.id=this_week.uid
";

processOrderBy($q,'this_week_sum','DESC');

$field=array(
	'staff_name'=>array('title'=>'姓名'),
	'this_week_sum'=>array('title'=>'本周','content'=>'{this_week_sum}({this_week_avg})'),
	'this_month_sum'=>array('title'=>'本月','content'=>'{this_month_sum}({this_month_avg})'),
	'this_year_sum'=>array('title'=>'本年','content'=>'{this_year_sum}({this_year_avg})')
);

$work_hour_stat=fetchTableArray($q,$field);
?>