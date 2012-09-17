<?php
model('case');
	
$q="
SELECT
	case.id,case.name,case.num,case.stage,case.time_contract,case.is_reviewed,case.filed,
	if(case.type_lock=1 AND case.client_lock=1 AND case.lawyer_lock=1 AND case.fee_lock=1,1,0) AS locked,
	contribute_allocate.contribute_sum,
	uncollected.uncollected,
	lawyers.lawyers
FROM 
	`case`
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
		LEFT JOIN
		(
			SELECT `case`, SUM(amount) AS amount_sum FROM account WHERE reviewed=0 GROUP BY `case`
		)account_grouped
		USING (`case`)
	)uncollected
	ON case.id=uncollected.case

WHERE case.display=1 AND case.id>=20
";

$search_bar=processSearch($q,array('num'=>'案号','name'=>'名称','lawyers.lawyers'=>'主办律师'));

processOrderby($q,'case.time_contract','DESC',array('case.name','lawyers'));

$listLocator=processMultiPage($q);

$field=array(
	'time_contract'=>array('title'=>'案号','td_title'=>'width="180px"','content'=>'<a href="case?edit={id}">{num}</a>'),
	'name'=>array('title'=>'案名','content'=>'{name}<span class="right"><a href="javascript:showWindow(\'account?add&case={id}\')">+<span>'),
	'lawyers'=>array('title'=>'主办律师','td_title'=>'width="100px"'),
	'is_reviewed'=>array('title'=>'状态','td_title'=>'width="75px"','eval'=>true,'content'=>"
		return case_getStatus('{is_reviewed}','{locked}','{contribute_sum}','{uncollected}','{filed}');
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