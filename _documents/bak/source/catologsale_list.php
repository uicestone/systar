<?php
if(!defined('IN_UICE'))
	exit('no permission');
	
$q="SELECT 
	client.id,client.name,client.time,client.address,client.zipcode,
	if(plaintiff like CONCAT('%',name,'%'),'原告','被告') AS side,
	client_catologsale.status,
	court.date,court.matter,court.plaintiff,court.defendant,court.court,court.room
FROM 
	(
		client_catologsale
		LEFT JOIN client ON client_catologsale.id=client.id
	)
	LEFT JOIN court ON client_catologsale.case=court.id
WHERE 1=1";

$search_bar=processSearch($q,array('client.name'=>'客户'));

processOrderby($q,'time','DESC');

$listLocator=processMultiPage($q);

$field=Array(
	'checkbox'=>array('title'=>'','content'=>'<input type="checkbox" name="client[{id}]" >','td_title'=>' width=38px'),
	'client.name'=>array('title'=>'名称','surround'=>array('mark'=>'a','href'=>'/catologsale.php?edit={id}')),
	'client.time'=>array('title'=>'时间','td_title'=>'width="70px"','eval'=>true,'content'=>"
		return date('m-d','{time}');
	"),
	'client.address'=>array('title'=>'地址','td_title'=>'width="240px"',
		'td'=>'class="ellipsis" onmouseover="$(this).toggleClass(\'ellipsis\')" onmouseout="$(this).toggleClass(\'ellipsis\')"'
	),
	'client.zipcode'=>array('title'=>'邮编'),
	'side'=>array('title'=>'地位'),
	'court.date'=>array('title'=>'开庭日期'),
	'client_catologsale.status'=>array('title'=>'直邮状态','td_title'=>'width="90px"','eval'=>true,'content'=>'
		switch(\'{status}\'){
			case \'未处理\':return "<div style=\"background:#CFC;text-align:center\">未处理</div>";
			case \'已打印\':return "<div style=\"background:#FCC;text-align:center\">已打印</div>";
			case \'已寄出\':return "<div style=\"background:#CCF;text-align:center\">已寄出</span>";
			case \'已退回\':return "<div style=\"background:#FCF;text-align:center\">已退回</span>";
			case \'未查明\':return "<div style=\"background:#FFC;text-align:center\">未查明</span>";
			case \'无价值\':return "<div style=\"background:#CCC;text-align:center\">无价值</span>";
			case \'已过期\':return "<div style=\"background:#CCC;text-align:center\">已过期</span>";
		}
	'),
);
$submitBar=array(
'head'=>'<div style="float:left">'.
			'<button type="button" onclick="post(\'print\',true)">标记打印</button>'.
			(option('in_search_mod')?'<button type="button" value="searchCancel" onclick="redirectPara(this)">取消搜索</button>':'').
		'</div>'.
		'<div style="float:right;">'.
			$listLocator.
		'</div>',
);

exportTable($q,$field,$submitBar,true);

require 'html/catologsale_list_sidebar.php';
?>