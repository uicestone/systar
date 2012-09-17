<?php
$q_monthly_achievement="
	SELECT month,collect.sum AS collect,contract.sum AS contract
	FROM(
		SELECT FROM_UNIXTIME(time_occur,'%Y-%m') AS `month`,SUM(amount) AS sum
		FROM account 
		GROUP BY FROM_UNIXTIME(time_occur,'%Y-%m')
	)collect INNER JOIN
	(
		SELECT LEFT(case.time_contract,7) AS month,SUM(case_fee.fee) AS sum
		FROM case_fee INNER JOIN `case` ON case.id=case_fee.case
		GROUP BY LEFT(case.time_contract,7)
	)contract USING (month)
	WHERE LEFT(month,4)='".date('Y',$_G['timestamp'])."'
";

$monthly_collect=db_toArray($q_monthly_achievement);

$months=array_sub($monthly_collect,'month');
$collect=array_sub($monthly_collect,'collect');
$contract=array_sub($monthly_collect,'contract');

$series=array(
	array(
		'name'=>'创收',
		'data'=>$collect
	),
	array(
		'name'=>'签约',
		'data'=>$contract
	),
);

$months=json_encode($months);
$series=json_encode($series,JSON_NUMERIC_CHECK);
?>