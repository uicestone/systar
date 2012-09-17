<?php
$q="SELECT case_fee.id,case_fee.type,case_fee.fee,FROM_UNIXTIME(case_fee.pay_time,'%Y-%m-%d') AS pay_time,
	case.name AS case_name,case.id AS `case`,
	IF(account_grouped.amount_sum IS NULL,case_fee.fee,case_fee.fee-account_grouped.amount_sum) AS uncollected,
	clients.clients,
	lawyers.lawyers
FROM case_fee
	LEFT JOIN (
		SELECT `case_fee`,SUM(amount) AS amount_sum
		FROM account
		GROUP BY `case_fee`
	)account_grouped#根据case_fee分组求和的account
	ON case_fee.id=account_grouped.case_fee
	
	LEFT JOIN (
		SELECT case_client.case,GROUP_CONCAT(DISTINCT client.abbreviation) AS clients
		FROM case_client INNER JOIN client ON case_client.client=client.id
		WHERE client.classification='客户'
		GROUP BY case_client.case
	)clients
	ON clients.case=case_fee.case
	
	INNER JOIN `case` ON case.id=case_fee.case
	
	LEFT JOIN
	(
		SELECT `case`,GROUP_CONCAT(staff.name) AS lawyers
		FROM case_lawyer,staff 
		WHERE case_lawyer.lawyer=staff.id AND case_lawyer.role='主办律师'
		GROUP BY case_lawyer.`case`
	)lawyers
	ON `case_fee`.case=lawyers.`case`
		
WHERE case_fee.type<>'办案费'
	AND case_fee.reviewed=0
	AND (account_grouped.amount_sum IS NULL OR case_fee.fee-account_grouped.amount_sum>0)#款未到/未到齐
	AND case_fee.case IN (
		SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."'
	)
	AND case_fee.`case` NOT IN (
		SELECT id FROM `case` WHERE filed='已归档'
	)
	AND FROM_UNIXTIME(pay_time,'%Y-%m-%d')<'".$_G['date']."'
	AND case.filed<>'已归档'
";

processOrderby($q,'case_fee.pay_time');

$listLocator=processMultiPage($q);

$field=array(
	'type'=>array('title'=>'类别','td_title'=>'width="85px"'),
	'case_name'=>array('title'=>'案件','td_title'=>'width="25%"','content'=>'<a href="case?edit={case}" class="right" style="margin-left:10px;">查看</a>{case_name}'),
	'lawyers'=>array('title'=>'主办律师'),
	'fee'=>array('title'=>'预估','td_title'=>'width="100px"'),
	'pay_time'=>array('title'=>'时间','td_title'=>'width="100px"'),
	'uncollected'=>array('title'=>'未收','td_title'=>'width="100px"'),
	'clients'=>array('title'=>'客户')
);

$menu=array(
'head'=>'<div class="right">'.
			$listLocator.
		'</div>'
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$menu,true);
?>