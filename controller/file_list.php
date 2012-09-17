<?php
model('case');
	
$q="
SELECT
	case.id,case.name AS case_name,case.stage,case.time_contract,case.time_end,case.num,case.is_reviewed,case.filed,
	lawyers.lawyers,
	file_status_grouped.status,file_status_grouped.staff AS staff,FROM_UNIXTIME(file_status_grouped.time,'%Y-%m-%d %H:%i:%s') AS status_time,
	if(case.type_lock=1 AND case.client_lock=1 AND case.lawyer_lock=1 AND case.fee_lock=1,1,0) AS locked,
	contribute_allocate.contribute_sum,
	uncollected.uncollected,
	staff.name AS staff_name
FROM 
	`case` INNER JOIN case_num ON `case`.id=case_num.`case`

	LEFT JOIN
	(
		SELECT `case`,GROUP_CONCAT(staff.name) AS lawyers
		FROM case_lawyer,staff 
		WHERE case_lawyer.lawyer=staff.id AND case_lawyer.role='主办律师'
		GROUP BY case_lawyer.`case`
	)lawyers
	ON `case`.id=lawyers.`case`
	
	LEFT JOIN (
		SELECT * FROM (
			SELECT `case`,status,staff,time FROM file_status ORDER BY time DESC
		)file_status_ordered
		GROUP BY `case`
	)file_status_grouped 
	ON case.id=file_status_grouped.case
	
	LEFT JOIN staff ON file_status_grouped.staff=staff.id
	
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
		LEFT JOIN
		(
			SELECT `case`, SUM(amount) AS amount_sum FROM account WHERE reviewed=0 GROUP BY `case`
		)account_grouped
		USING (`case`)
	)uncollected
	ON case.id=uncollected.case
	
WHERE case.display=1 AND case.id>=20 AND case.filed='已归档'
";

$search_bar=processSearch($q,array('case_num_grouped.num'=>'案号','case.name'=>'名称','lawyers.lawyers'=>'主办律师'));

processOrderby($q,'time_contract','DESC',array('case.name','lawyers'));

$listLocator=processMultiPage($q);

$field=array(
	'num'=>array('title'=>'案号','td_title'=>'width="180px"','content'=>'<a href="case?edit={id}">{num}</a>'),
	'case_name'=>array('title'=>'案名'),
	'time_contract'=>array('title'=>'收案时间'),
	'time_end'=>array('title'=>'结案时间'),
	'lawyers'=>array('title'=>'主办律师','td_title'=>'width="100px"'),
	'status'=>array('title'=>'状态','td'=>'title="{status_time}"','eval'=>true,'content'=>"
		return case_getStatus('{is_reviewed}','{locked}','{contribute_sum}','{uncollected}','{filed}').' {status}';
	"),
	'staff_name'=>array('title'=>'人员')
);

$submitBar=array(
'head'=>'<div class="right">'.
			$listLocator.
		'</div>'
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$submitBar);
?>