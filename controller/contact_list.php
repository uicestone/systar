<?php
model('client');

if(is_posted('delete')){
	$_POST=array_trim($_POST);
	client_delete($_POST['contact_check']);
}

$q="SELECT client.id,client.name,client.abbreviation,client.work_for,client.position,client.comment,
		phone.content AS phone,address.content AS address
	FROM `client` LEFT JOIN (
		SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type IN('手机','固定电话') GROUP BY client
	)phone ON client.id=phone.client
	LEFT JOIN (
		SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type='地址' GROUP BY client
	)address ON client.id=address.client

 WHERE display=1";

if(got('opposite')){
	$q.=" AND classification='相对方'";

}else{
	$q.=" AND classification='联系人'";
}

$search_bar=processSearch($q,array('name'=>'姓名','type'=>'类型','work_for'=>'单位','address'=>'地址'));

processOrderby($q,'time','DESC',array('abbreviation','address','comment'));

$listLocator=processMultiPage($q);

$field=array(
	'abbreviation'=>array('title'=>'名称','content'=>'<input type="checkbox" name="contact_check[{id}]" />
	<a href="javascript:showWindow(\'contact?edit={id}\')" title="{name}">{abbreviation}</a>',
		'td'=>'class="ellipsis"'
	),
	'work_for'=>array('title'=>'单位'),
	'position'=>array('title'=>'职务'),
	'phone'=>array('title'=>'电话','td'=>'class="ellipsis" title="{phone}"'
	),
	'address'=>array('title'=>'地址','td'=>'class="ellipsis" title="{address}"'
	),
	'comment'=>array('title'=>'备注','td'=>'class="ellipsis"','eval'=>true,'content'=>"
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