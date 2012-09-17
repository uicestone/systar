<?php
$q="SELECT 
		property.id AS property,
		property.name AS name,
		property.admin AS admin,
		property_status.status AS status,
		if(property_status.is_out=1,'是','') AS is_out,
		FROM_UNIXTIME(property_status.time,'%Y-%m-%d') as time,
		property_status.usingPerson AS usingPerson,
		property.comment
	FROM property,property_status
	WHERE property.id=property_status.property 
		AND property_status.id IN 
		(SELECT max(id) FROM property_status GROUP BY property)";

$searchBar=processSearch($q,array('name'=>'物品','admin'=>'管理人'));

processOrderby($q,'time','DESC',array('property'));

$listLocator=processMultiPage($q);

$field=Array(
	'property'=>'序号',
	'name'=>array('title'=>'物品','surround'=>array('mark'=>'a','href'=>'/property?view={property}','target'=>'blank')),
	'time'=>'更新时间','admin'=>'管理人',
		'status'=>array('title'=>'目前状态','content'=>'{status} <a href="/property?addStatus={property}" style="font-size:10px;">更新</a>'),
	'usingPerson'=>'经手人',
	'comment'=>'备注');


$submitBar=array(
'head'=>'<div style="float:left;">'.
			'<input type="submit" name="delete" value="删除" />'.
			(option('in_search_mod')?'<button type="button" value="searchCancel" onclick="redirectPara(this)">取消搜索</button>':'').
		'</div>'.
		'<div style="float:right;">'.
			$listLocator.
		'</div>',
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$submitBar,true);
?>