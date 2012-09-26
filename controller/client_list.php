<?php
if(is_posted('delete')){
	$_POST=array_trim($_POST);
	client_delete($_POST['client_check']);
}

$q="
SELECT client.id,client.name,client.abbreviation,client.time,client.comment,
	phone.content AS phone,address.content AS address
FROM `client` 
	LEFT JOIN (
		SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type IN('手机','固定电话') GROUP BY client
	)phone ON client.id=phone.client
	LEFT JOIN (
		SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type='地址' GROUP BY client
	)address ON client.id=address.client
WHERE display=1 AND classification='客户'
";

$q_rows="
	SELECT COUNT(client.id)
	FROM `client` 
	WHERE display=1 AND classification='客户'
";

if(got('potential')){
	$q.=" AND type='潜在客户'";

}else{
	$q.="
		AND type='成交客户'
		AND client.id IN (SELECT client FROM case_client WHERE `case` IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."'))
";
}

$search_bar=processSearch($q,array('name'=>'姓名','work_for'=>'单位','address'=>'地址','comment'=>'备注'));

processOrderby($q,'time','DESC',array('abbreviation','type','address','comment'));

$listLocator=processMultiPage($q);

$field=array(
	'abbreviation'=>array('title'=>'名称','content'=>'<input type="checkbox" name="client_check[{id}]" />
	<a href="javascript:showWindow(\'client?edit={id}\')" title="{name}">{abbreviation}</a>',
		'td'=>'class="ellipsis"'
	),
	'phone'=>array('title'=>'电话','td'=>'class="ellipsis" title="{phone}"'),
	'address'=>array('title'=>'地址','td_title'=>'width="240px"',
		'td'=>'class="ellipsis" title="{address}"'
	),
	'comment'=>array('title'=>'备注','td'=>'class="ellipsis" title="{comment}"','eval'=>true,'content'=>"
		return str_getSummary('{comment}',50);
	",
	)
);
$submitBar=array(
'head'=>'<div class="left">'.
			'<input type="submit" name="delete" value="删除" />'.
		'</div>'.
		'<div class="right">'.
			$listLocator.
		'</div>'
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$submitBar,true);
?>