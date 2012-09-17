<?php
$q="
SELECT
	case.id,case.name,case.num,case.stage,case.stage,
	lawyers.lawyers
FROM 
	`case`

	LEFT JOIN
	(
		SELECT `case`,GROUP_CONCAT(staff.name) AS lawyers
		FROM case_lawyer,staff 
		WHERE case_lawyer.lawyer=staff.id AND case_lawyer.role='主办律师'
		GROUP BY case_lawyer.`case`
	)lawyers
	ON `case`.id=lawyers.`case`
	WHERE case.display=1 AND case.id>=20 AND case.lawyer_lock=0 AND case.is_reviewed=0";

$search_bar=processSearch($q,array('case_num_grouped.num'=>'案号','case.name'=>'名称','lawyers.lawyers'=>'主办律师'));

processOrderby($q,'case.time_contract','DESC',array('case.name','lawyers'));

$listLocator=processMultiPage($q);

$field=array(
	'time_contract'=>array('title'=>'案号','td_title'=>'width="180px"','content'=>'<a href="case?edit={id}">{num}</a>'),
	'name'=>array('title'=>'案名','content'=>'{name}'),
	'lawyers'=>array('title'=>'主办律师','td_title'=>'width="100px"')
);

$submitBar=array(
'head'=>'<div class="right">'.
			$listLocator.
		'</div>'
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$submitBar);

require 'view/case_list_sidebar.htm';
?>