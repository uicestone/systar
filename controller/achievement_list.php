<?php
$q="
SELECT case_fee_collected.*,
	GROUP_CONCAT(DISTINCT client.abbreviation) AS clients,
	case.name AS case_name,
	ROUND(case_fee_collected.collected*case_lawyer.contribute,2) AS contribute_collected,
	ROUND(case_fee_collected.collected*case_lawyer.contribute*0.15,2) AS bonus,
	case_lawyer.role
FROM
(
	SELECT case_fee.id,case_fee.case,case_fee.type,
		case_fee.fee,FROM_UNIXTIME(case_fee.pay_time,'%Y-%m-%d') AS pay_time,
		SUM(account.amount) AS collected,FROM_UNIXTIME(account.time_occur,'%Y-%m-%d') AS time_occur,
		IF(SUM(account.amount) IS NULL,case_fee.fee,case_fee.fee-SUM(account.amount)) AS uncollected
	FROM case_fee
	LEFT JOIN account ON case_fee.id=account.case_fee
	WHERE case_fee.type<>'办案费'
";

$date_range_bar=dateRange($q,'account.time_occur');	

$q.="	GROUP BY case_fee.id
)case_fee_collected
	INNER JOIN case_client ON case_fee_collected.case=case_client.case
	INNER JOIN client ON case_client.client=client.id
	INNER JOIN case_lawyer ON case_fee_collected.case=case_lawyer.case
	INNER JOIN case_num ON case_fee_collected.case=case_num.case
	INNER JOIN `case` ON case_fee_collected.case=case.id
WHERE case_lawyer.lawyer='".$_SESSION['id']."'
	AND client.classification='客户'
	AND case_lawyer.role NOT IN ('督办合伙人','律师助理')
";

$q.=' GROUP BY case_fee_collected.id,case_lawyer.lawyer,case_lawyer.role
	HAVING collected>0';

processOrderby($q,'case_fee_collected.pay_time','DESC');

$listLocator=processMultiPage($q);

$field=array(
	'type'=>array('title'=>'类别','td_title'=>'width="85px"'),
	'case_name'=>array('title'=>'案件','td_title'=>'width="25%"','content'=>'<a href="case?edit={case}" class="right" style="margin-left:10px;">查看</a>{case_name}'),
	'fee'=>array('title'=>'预估','td_title'=>'width="100px"','td'=>'title="{pay_time}"'),
	'collected'=>array('title'=>'实收','td_title'=>'width="100px"','td'=>'title="{time_occur}"'),
	'role'=>array('title'=>'角色'),
	'contribute_collected'=>array('title'=>'贡献'),
	'bonus'=>array('title'=>'奖金'),
	'clients'=>array('title'=>'客户')
);

$menu=array(
'head'=>'<div class="right">'.
			$listLocator.
		'</div>'
);

$month_start_timestamp=strtotime(date('Y-m',$_G['timestamp']).'-1');
$month_end_timestamp=mktime(0,0,0,date('m',$_G['timestamp'])+1,1,date('Y',$_G['timestamp']));

$achievement_sum=array(
	'_field'=>array(
		'field'=>'本月',
		'total'=>'全所',
		'my'=>'主办',
		'contribute'=>'贡献'
	),
	
	'contracted'=>array(
		'field'=>'签约',
		'total'=>achievementSum('contracted','total',$month_start_timestamp),
		'my'=>achievementSum('contracted','my',$month_start_timestamp),
		'contribute'=>achievementSum('contracted','contribute',$month_start_timestamp)
	),
	
	'estimated'=>array(
		'field'=>'预计',
		'total'=>achievementSum('estimated','total',$month_start_timestamp,$month_end_timestamp),
		'my'=>achievementSum('estimated','my',$month_start_timestamp,$month_end_timestamp),
		'contribute'=>achievementSum('estimated','contribute',$month_start_timestamp,$month_end_timestamp)
	),
	
	'collected'=>array(
		'field'=>'到账',
		'total'=>achievementSum('collected','total',$month_start_timestamp),
		'my'=>achievementSum('collected','my',$month_start_timestamp),
		'contribute'=>achievementSum('collected','contribute',$month_start_timestamp)
	)
);

$achievement=achievementSum('collected','contribute',option('date_range/from_timestamp'),option('date_range/to_timestamp'),false);
$bonus=ROUND($achievement*0.15,2);

$achievement_dashboard=array(
	'_field'=>array(
		'贡献',
		'奖金'
	),
	array(
		$achievement,
		$bonus
	)
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$menu,true);
?>