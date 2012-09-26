<?php
$q="
	SELECT 
		case.id,case.first_contact,case.num,case.query_type AS type,case.summary,case.comment,
		client.abbreviation AS client_name,case_client.client,
		GROUP_CONCAT(staff.name) AS staff_name,
		client_source.type AS source
	FROM `case`
		LEFT JOIN case_client ON case.id=case_client.case
		LEFT JOIN client ON client.id=case_client.client
		LEFT JOIN case_lawyer ON case.id=case_lawyer.case AND (case_lawyer.role='接洽律师'  OR case_lawyer.role='接洽律师（次要）')
		LEFT JOIN staff ON staff.id=case_lawyer.lawyer
		LEFT JOIN client_source ON case.source=client_source.id 
	WHERE case.display=1 AND case.is_query=1
";

if(got('mine')){
	$q.=" AND case_lawyer.lawyer='".$_SESSION['id']."'";
}

if(got('filed')){
	$q.=" AND case.filed=1";
}else{
	$q.=" AND case.filed=0";
}

$search_bar=processSearch($q,array('client.name'=>'咨询人'));

$q.=" GROUP BY case.id";

processOrderby($q,'first_contact','DESC');

$listLocator=processMultiPage($q);

$field=array(
	'first_contact'=>array('title'=>'日期','td_title'=>'width="95px"'),
	'num'=>array('title'=>'编号','td_title'=>'width="180px"','surround'=>array('mark'=>'a','href'=>'case?edit={id}')),
	'client_name'=>array('title'=>'咨询人','surround'=>array('mark'=>'a','href'=>'javascript:showWindow(\'client?edit={client}\')')),
	'type'=>array('title'=>'方式','td_title'=>'width="80px"'),
	'source'=>array('title'=>'来源'),
	'staff_name'=>array('title'=>'接洽人'),
	'summary'=>array('title'=>'概况','td'=>'class="ellipsis" title="{summary}"'),
	'comment'=>array('title'=>'备注','td'=>'class="ellipsis" title="{comment}"')
);

$submitBar=array(
'head'=>'<div class="right">'.
			$listLocator.
		'</div>'
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$submitBar);
?>