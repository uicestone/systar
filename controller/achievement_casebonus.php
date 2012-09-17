<?php
$q="
SELECT staff.name AS staff_name, ROUND(SUM(case_collect.amount*case_contribute.contribute),2) AS contribute_sum,ROUND(SUM(case_collect.amount*case_contribute.contribute*0.15),2) AS bonus_sum
FROM (
	SELECT  `case` , SUM( amount ) amount
	FROM account
	WHERE name IN ('律师费','顾问费')
		AND `case` IN (
			SELECT id FROM `case` WHERE lawyer_lock=1";

if(got('contribute_type','actual')){
	$q.=" AND case.filed='已归档'";
	$date_range_bar=dateRange($q,'case.time_end',false);
}else{
	$date_range_bar=dateRange($q,'account.time_occur');
}

$q.="		)#律师锁定的案子才能计算奖金";

$q.="
	GROUP BY  `case`
)case_collect
INNER JOIN (
	SELECT  `case` , lawyer, SUM( contribute ) AS contribute
	FROM case_lawyer
WHERE 1=1
";

if(got('contribute_type','actual')){
	$q.=" AND role = '实际贡献'";
}elseif(got('contribute_type','fixed')){
	$q.=" AND role<>'实际贡献'";
}

$q.="	GROUP BY  `case` , lawyer
)case_contribute ON case_collect.case = case_contribute.case
INNER JOIN staff ON staff.id = case_contribute.lawyer
WHERE case_contribute.contribute>0
";

$q.="GROUP BY case_contribute.lawyer";

processOrderby($q,'staff.id','ASC',array('staff_name'));

$q_rows="SELECT COUNT(id) FROM staff";

$listLocator=processMultiPage($q,$q_rows);

$field=array(
	'staff_name'=>array('title'=>'人员'),
	'contribute_sum'=>array('title'=>'合计贡献'),
	'bonus_sum'=>array('title'=>'合计奖金')
);

$menu=array(
'head'=>'<div class="right">'.
			$listLocator.
		'</div>'
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$menu,true);
?>