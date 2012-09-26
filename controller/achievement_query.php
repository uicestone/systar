<?php
//TODO 新的咨询统计
$q_monthly_queries="
SELECT month,queries,filed_queries,live_queries,cases
FROM (
	SELECT LEFT(date_start,7) AS month, COUNT(id) AS queries, SUM(IF(filed=1,1,0)) AS filed_queries, SUM(IF(filed='洽谈',1,0)) AS live_queries
	FROM query 
	WHERE LEFT(date_start,4)='".date('Y',$_G['timestamp'])."'
	GROUP BY LEFT(date_start,7)
)query INNER JOIN (
	SELECT LEFT(time_contract,7) AS month, COUNT(id) AS cases
	FROM `case`
	WHERE LEFT(time_contract,4)='".date('Y',$_G['timestamp'])."'
	GROUP BY LEFT(time_contract,7)
)`case` USING(month)";
$monthly_queries=db_toArray($q_monthly_queries);
$chart_monthly_queries_catogary=json_encode(array_sub($monthly_queries,'month'));
$chart_monthly_queries_series=array(
	array('name'=>'总量','data'=>array_sub($monthly_queries,'queries')),
	array('name'=>'归档','color'=>'#AAA','data'=>array_sub($monthly_queries,'filed_queries')),
	array('name'=>'在谈','data'=>array_sub($monthly_queries,'live_queries')),
	array('name'=>'新增案件','data'=>array_sub($monthly_queries,'cases'))

);
$chart_monthly_queries_series=json_encode($chart_monthly_queries_series,JSON_NUMERIC_CHECK);

$q_personally_queries="
	SELECT staff.name AS staff_name, COUNT(query.id) AS queries, SUM(IF(filed='归档',1,0)) AS filed_queries, SUM(IF(filed='洽谈',1,0)) AS live_queries
	FROM query INNER JOIN staff ON staff.id=query.lawyer
	WHERE LEFT(date_start,4)='".date('Y',$_G['timestamp'])."'
	GROUP BY lawyer
	ORDER BY live_queries DESC, queries DESC
";
$personally_queries=db_toArray($q_personally_queries);

$chart_personally_queries_catogary=json_encode(array_sub($personally_queries,'staff_name'));
$chart_personally_queries_series=array(
	array('name'=>'归档','color'=>'#AAA','data'=>array_sub($personally_queries,'filed_queries')),
	array('name'=>'在谈','data'=>array_sub($personally_queries,'live_queries'))

);
$chart_personally_queries_series=json_encode($chart_personally_queries_series,JSON_NUMERIC_CHECK);

$q_personally_type_queries="
	SELECT staff.name AS staff_name, COUNT(query.id) AS queries, SUM(IF(type='面谈咨询',1,0)) AS face_queries, SUM(IF(type='电话咨询',1,0)) AS call_queries, SUM(IF(type='网上咨询',1,0)) AS online_queries
	FROM query INNER JOIN staff ON staff.id=query.lawyer
	WHERE LEFT(date_start,4)='".date('Y',$_G['timestamp'])."'
	GROUP BY lawyer
	ORDER BY face_queries DESC, call_queries DESC, online_queries DESC
";
$personally_type_queries=db_toArray($q_personally_type_queries);

$chart_personally_type_queries_catogary=json_encode(array_sub($personally_type_queries,'staff_name'));
$chart_personally_type_queries_series=array(
	array('name'=>'网上咨询','data'=>array_sub($personally_type_queries,'online_queries')),
	array('name'=>'电话咨询','data'=>array_sub($personally_type_queries,'call_queries')),
	array('name'=>'面谈咨询','data'=>array_sub($personally_type_queries,'face_queries'))

);
$chart_personally_type_queries_series=json_encode($chart_personally_type_queries_series,JSON_NUMERIC_CHECK);
?>