<?php
$q="
SELECT
	case.id,case.name,case.num,case.stage,case.time_contract,
	case.is_reviewed,case.apply_file,case.is_query,
	case.type_lock*case.client_lock*case.lawyer_lock*case.fee_lock AS locked,
	case.finance_review,case.info_review,case.manager_review,case.filed,
	contribute_allocate.contribute_sum,
	uncollected.uncollected,
	schedule_grouped.id AS schedule,schedule_grouped.name AS schedule_name,schedule_grouped.time_start,schedule_grouped.username AS schedule_username,
	plan_grouped.id AS plan,plan_grouped.name AS plan_name,FROM_UNIXTIME(plan_grouped.time_start,'%m-%d') AS plan_time,plan_grouped.username AS plan_username,
	lawyers.lawyers
FROM 
	`case`

	LEFT JOIN
	(
		SELECT * FROM(
			SELECT * FROM `schedule` WHERE completed=1 AND display=1 ORDER BY time_start DESC LIMIT 1000
		)schedule_id_desc 
		GROUP BY `case`
	)schedule_grouped
	ON `case`.id = schedule_grouped.`case`
	
	LEFT JOIN
	(
		SELECT * FROM(
			SELECT * FROM `schedule` WHERE completed=0 AND display=1 AND time_start>UNIX_TIMESTAMP() ORDER BY time_start LIMIT 1000
		)schedule_id_asc 
		GROUP BY `case`
	)plan_grouped
	ON `case`.id = plan_grouped.`case`
	
	LEFT JOIN
	(
		SELECT `case`,GROUP_CONCAT(staff.name) AS lawyers
		FROM case_lawyer,staff 
		WHERE case_lawyer.lawyer=staff.id AND case_lawyer.role='主办律师'
		GROUP BY case_lawyer.`case`
	)lawyers
	ON `case`.id=lawyers.`case`
	
	LEFT JOIN 
	(
		SELECT `case`,SUM(contribute) AS contribute_sum
		FROM case_lawyer
		GROUP BY `case`
	)contribute_allocate
	ON `case`.id=contribute_allocate.case
	
	LEFT JOIN
	(
		SELECT `case`,IF(amount_sum IS NULL,fee_sum,fee_sum-amount_sum) AS uncollected FROM
		(
			SELECT `case`,SUM(fee) AS fee_sum FROM case_fee WHERE type<>'办案费' AND reviewed=0 GROUP BY `case`
		)case_fee_grouped
		INNER JOIN
		(
			SELECT `case`, SUM(amount) AS amount_sum FROM account GROUP BY `case`
		)account_grouped
		USING (`case`)
	)uncollected
	ON case.id=uncollected.case
	
WHERE case.display=1 AND case.id>=20 AND is_query=0
";

//此query过慢，用其简化版计算总行数
$q_rows="
SELECT
	COUNT(id)
FROM 
	`case`
WHERE case.display=1 AND case.id>=20 AND case.filed=0 AND case.is_query=0
";

$condition='';

if(got('host')){
	$condition.="AND case.apply_file=0 AND case.id IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role='主办律师')";

}elseif(got('consultant')){
	$condition.="AND case.apply_file=0 AND classification='法律顾问' AND (case.id IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."') OR case.uid='".$_SESSION['id']."')";

}elseif(got('etc')){
	$condition.="AND case.apply_file=0 AND classification<>'法律顾问' AND (case.id IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role<>'主办律师') OR case.uid='".$_SESSION['id']."')";
	
}elseif(got('file')){
	$condition.="AND case.apply_file=1 AND classification<>'法律顾问' AND (case.id IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role<>'主办律师') OR case.uid='".$_SESSION['id']."')";
	
}elseif(!is_logged('developer')){
	$condition.="AND (case.id IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role IN ('接洽律师','接洽律师（次要）','主办律师','协办律师','律师助理','督办合伙人')) OR case.uid='".$_SESSION['id']."')";
}

$search_bar=processSearch($condition,array('case.num'=>'案号','case.type'=>'类别','case.name'=>'名称','lawyers.lawyers'=>'主办律师'));

processOrderby($condition,'time_contract','DESC',array('case.name','lawyers'));

$q.=$condition;
$q_rows.=$condition;

$listLocator=processMultiPage($q,$q_rows);

$field=array(
	'time_contract'=>array('title'=>'案号','td_title'=>'width="180px"','td'=>'title="立案时间：{time_contract}"','content'=>'<a href="case?edit={id}">{num}</a>'),
	'name'=>array('title'=>'案名','content'=>'{name}'),
	'lawyers'=>array('title'=>'主办律师','td_title'=>'width="100px"'),
	'schedule_grouped.time_start'=>array('title'=>'最新日志','eval'=>true,'content'=>"
		return '<a href=\"javascript:showWindow(\'schedule?add&case={id}\')\">+</a> <a href=\"schedule?list&case={id}\" title=\"{schedule_name}\">'.str_getSummary('{schedule_name}').'</a>';
	"),
	'plan_grouped.time_start'=>array('title'=>'最近提醒','eval'=>true,'content'=>"
		return '<a href=\"javascript:showWindow(\'schedule?add&case={id}&completed=0\')\">+</a> {plan_time} <a href=\"schedule?list&plan&case={id}\" title=\"{plan_name}\">'.str_getSummary('{plan_name}').'</a>';
	"),
	'is_reviewed'=>array('title'=>'状态','td_title'=>'width="75px"','eval'=>true,'content'=>"
		return case_getStatus('{is_reviewed}','{locked}',{apply_file},{is_query},{finance_review},{info_review},{manager_review},{filed},'{contribute_sum}','{uncollected}').' {status}';
	",'orderby'=>false)
);

$submitBar=array(
'head'=>'<div class="right">'.
			$listLocator.
		'</div>'
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$submitBar);
?>