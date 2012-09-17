<?php
$q="SELECT * 
	FROM `property`,`property_status` 
	WHERE property.id=property_status.property 
		AND property.id='".$_GET['view']."'";

processOrderby($q,'time','DESC');

$field=Array('property'=>'序号','num'=>'编号','name'=>'物品','status'=>'目前状态','time'=>'时间','usingPerson'=>'经手人','comment'=>'备注');

exportTable($field, $q);
?>