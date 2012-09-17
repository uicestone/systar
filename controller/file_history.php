<?php
$q="SELECT 
		file.id AS file,
		file.`case` AS `case`,
		file.lawyer AS lawyer,
		file.client AS client,
		file_status.status AS status,
		file.date_start AS date_start,
		file_status.time AS time,
		file_status.person AS person,
		file.comment AS comment
	FROM file,file_status
	WHERE file.id=file_status.file 
		AND file_status.id IN 
		(SELECT max(id) FROM file_status GROUP BY file)";

$search_bar=processSearch($q,array('case'=>'案件','lawyer'=>'律师','client'=>'客户'));

processOrderby($q,'time','DESC',array('case','client','lawyer','status'));

$listLocator=processMultiPage($q);

$field=Array(
	'file'=>array('title'=>'序号','td_title'=>'width=50px'),
	'case'=>array('title'=>'案件','surround'=>array('mark'=>'a','href'=>'/file?view={file}','target'=>'blank')),
	'client'=>'客户','lawyer'=>'承办律师',
	'date_start'=>array('title'=>'收案日期','eval'=>true,'content'=>"
		return date('Y年m月d日',{date_start});
	"),
	'status'=>array('title'=>'状态','surround'=>array('mark'=>'a','href'=>"/file?addStatus={file}")),
	'time'=>array('title'=>'更新时间','eval'=>true,'content'=>"
		return date('Y年m月d日',{time});
	"),
	'comment'=>'备注'
);

$submitBar=array(
'head'=>'<div style="float:left;">'.
			(option('in_search_mod')?'<button type="button" value="searchCancel" onclick="redirectPara(this)">取消搜索</button>':'').
		'</div>'.
		'<div style="float:right;">'.
			$listLocator.
		'</div>',
);

exportTable($q,$field,$submitBar,true);

require 'view/file_list_sidebar.htm'
?>