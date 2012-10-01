<?php
if(is_posted('review_selected') && is_logged('partner')){
	//在列表中批量审核所选日志
	schedule_review_selected();
}

$q="
	SELECT
		schedule.id,schedule.name,schedule.content,schedule.experience, schedule.time_start,schedule.hours_own,schedule.hours_checked,schedule.comment,schedule.place,
		case.id AS `case`,case.name AS case_name,
		staff.name AS staff_name,staff.id AS staff,
		if(MAX(case_lawyer.role)='督办合伙人',1,0) AS review_permission

		#imperfect 2012/7/13 MAX ENUM排序依据为字符串，并非INDEX

	FROM schedule INNER JOIN `case` ON schedule.case=case.id
		INNER JOIN case_lawyer ON case.id=case_lawyer.case
		LEFT JOIN staff ON staff.id = schedule.uid
	WHERE case_lawyer.lawyer='".$_SESSION['id']."'
		AND schedule.display=1 AND schedule.completed=".(got('plan')?'0':'1')."
";

//TODO schedule_list列表效率
$q_rows="
	SELECT COUNT(schedule.id) 
	FROM schedule
	WHERE 
";

$condition='';
if(got('case')){
	$condition.=" AND schedule.`case`='".intval($_GET['case'])."'";
}

if(got('staff')){
	$condition.=" AND schedule.`uid`='".intval($_GET['staff'])."'";
}

if(got('mine')){
	$condition.=" AND schedule.`uid`='".$_SESSION['id']."'";
}

if(got('client')){
	$condition.=" AND schedule.client='".intval($_GET['client'])."'";
}

$q.=$condition;

$search_bar=processSearch($q,array('case.name'=>'案件','staff.name'=>'人员'));

$date_range_bar=dateRange($q,'time_start');

$q.="
	GROUP BY schedule.id
	ORDER BY FROM_UNIXTIME(time_start,'%Y%m%d') ".(got('plan')?'ASC':'DESC').",schedule.uid,time_start ".(got('plan')?'ASC':'DESC')."
";

$field=array(
	'checkbox'=>array('title'=>'<input type="checkbox" name="schedule_checkall">','content'=>'<input type="checkbox" name="schedule_check[{id}]" >','td_title'=>' width=38px','orderby'=>false),

	'case.id'=>array('title'=>'案件','content'=>'{case_name}<p style="font-size:11px;text-align:right;"><a href="schedule?list&case={case}">本案日志</a> <a href="case?edit={case}">案件</a></p>','orderby'=>false),

	'staff_name'=>array('title'=>'人员','content'=>'<a href="schedule?list&staff={staff}"> {staff_name}</a>','td_title'=>'width="60px"','orderby'=>false),

	'name'=>array('title'=>'标题','eval'=>true,'content'=>"
		return '<a href=\"javascript:showWindow(\'schedule?edit={id}\')\" title=\"{name}\">'.str_getSummary('{name}').'</a>';
	",'orderby'=>false),

	'content'=>array('title'=>'内容','eval'=>true,'content'=>"
		return '<div title=\"{content}\">'.str_getSummary('{content}').'&nbsp;'.'</div>';
	",'orderby'=>false),

	'schedule_experience'=>array('title'=>'心得','eval'=>true,'content'=>"
		return ({review_permission}||\$_SESSION['id']=='{staff}')?'<div title=\"{experience}\">'.str_getSummary('{experience}').'&nbsp;'.'</div>':'-';
	",'orderby'=>false),

	'time_start'=>array('title'=>'时间','td_title'=>'width="60px"','eval'=>true,'content'=>"
		return date('m-d H:i',{time_start});
	",'orderby'=>false),

	'hours_own'=>array('title'=>'时长','td_title'=>'width="55px"','eval'=>true,'content'=>"
		if('{hours_checked}'==''){
			return '<span class=\"hours_own'.({review_permission}?' editable':'').'\" id={id} name=\"hours\" title=\"自报：{hours_own}\">{hours_own}</span>';
		}else{
			return '<span class=\"hours_checked'.({review_permission}?' editable':'').'\" id={id} name=\"hours\" title=\"自报：{hours_own}\">{hours_checked}</span>';
		}
	",'orderby'=>false),

	'comment'=>array('title'=>'评语','eval'=>true,'content'=>"
		if({review_permission}){
			return '<textarea name=\"schedule_list_comment[{id}]\" style=\"width:95%;height:70%\">{comment}</textarea>';
		}else{
			if(\$_SESSION['id']=='{staff}'){
				return '<div title=\"{comment}\">'.str_getSummary('{comment}').'&nbsp;'.'</div>';
			}else{
				return '-';
			}
		}
		
	",'orderby'=>false)
);

if(got('mine')){
	unset($field['staff_name']);
}

if(is_posted('export')){
	$field=array(
		'name'=>array('title'=>'标题'),
		'content'=>array('title'=>'内容'),
		'time_start'=>array('title'=>'时间','td_title'=>'width="60px"','eval'=>true,'content'=>"
			return date('m-d H:i',{time_start});
		",'orderby'=>false),
		'hours_own'=>array('title'=>'自报小时'),
		'staff_name'=>array('title'=>'律师')
	);
}else{
	$listLocator=processMultiPage($q);
}

$table_array=fetchTableArray($q,$field);

if(is_posted('export')){
	require 'view/schedule_billdoc.php';

}else{
	$menu=array(
	'head'=>'<div class="left">'.
				(is_logged('partner')?'<input type="submit" name="review_selected" value="审核" />':'').
				'<input type="submit" name="export" value="导出" />'.
			'</div>'.
			'<div class="right">'.
				$listLocator.
			'</div>'
	);
	
	$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
}
?>