<?php
$q="SELECT *,FROM_UNIXTIME(time,'%Y-%m-%d') AS time 
	FROM `file`,`file_status` 
	WHERE file.id=file_status.file 
		AND file.id='".$_GET['view']."'";

processOrderby($q,'time','DESC');

$field=Array('file'=>'序号','client'=>'客户','case'=>'案件','lawyer'=>'承办律师','status'=>'状态','time'=>'时间','person'=>'借阅人','comment'=>'备注');

exportTable($q,$field);
?>