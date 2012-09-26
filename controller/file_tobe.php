<?php
model('case');

$q="
SELECT
	case.id,case.name,case.num,case.stage,case.time_contract,case.time_end,
	case.is_reviewed,case.apply_file,case.is_query,
	case.type_lock*case.client_lock*case.lawyer_lock*case.fee_lock AS locked,
	case.finance_review,case.info_review,case.manager_review,case.filed,
	contribute_allocate.contribute_sum,
	uncollected.uncollected,
	lawyers.lawyers

FROM 
	`case` LEFT JOIN
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
		LEFT JOIN
		(
			SELECT `case`, SUM(amount) AS amount_sum FROM account WHERE reviewed=0 GROUP BY `case`
		)account_grouped
		USING (`case`)
	)uncollected
	ON case.id=uncollected.case
	
WHERE case.display=1 AND case.id>=20 AND case.apply_file=1
";

$search_bar=processSearch($q,array('case_num_grouped.num'=>'案号','case.name'=>'名称','lawyers.lawyers'=>'主办律师'));

processOrderby($q,'case.time_contract','ASC',array('case.name','lawyers'));

$listLocator=processMultiPage($q);

$field=array(
	'num'=>array('title'=>'案号','content'=>'<a href="case?edit={id}">{num}</a>','td_title'=>'width="180px"'),
	'name'=>array('title'=>'案名','content'=>'{name}'),
	'time_contract'=>array('title'=>'收案时间'),
	'time_end'=>array('title'=>'结案时间'),
	'lawyers'=>array('title'=>'主办律师'),
	'status'=>array('title'=>'状态','td_title'=>'width="75px"','td'=>'title="{status_time}"','eval'=>true,'content'=>"
		return case_getStatus('{is_reviewed}','{locked}',{apply_file},{is_query},{finance_review},{info_review},{manager_review},{filed},'{contribute_sum}','{uncollected}').' {status}';
	")
);

$submitBar=array(
'head'=>'<div class="right">'.
			$listLocator.
		'</div>'
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$submitBar);
?>