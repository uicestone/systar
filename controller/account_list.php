<?php
model('achievement');

$q="
	SELECT
		account.id,account.time,account.name,account.amount,account.time_occur,
		client.abbreviation AS client_name
	FROM account LEFT JOIN client ON account.client=client.id
	WHERE amount<>0
";

if(!is_logged('finance')){
	$q.=" AND account.case IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role='主办律师')";
}

$search_bar=processSearch($q,array('client.name'=>'客户','account.name'=>'名目','account.amount'=>'金额'));

$date_range_bar=dateRange($q,'account.time_occur');

processOrderby($q,'time_occur','DESC');

$listLocator=processMultiPage($q);

$field=array(
	'time_occur'=>array('title'=>'日期','eval'=>true,'content'=>"
		return date('Y-m-d',{time_occur});
	"),
	'name'=>array('title'=>'名目','surround'=>array('mark'=>'a','href'=>'javascript:showWindow(\'account?edit={id}\')')),
	'_type'=>array('title'=>'方向','eval'=>true,'content'=>"
		if({amount}>0){
			return '<span style=\"color:#0F0\"><<</span>';
		}else{
			return '<span style=\"color:#F00\">>></span>';
		}
	",'td_title'=>'width="55px"','td'=>'style="text-align:center"'),
	'amount'=>array('title'=>'金额'),
	'client_name'=>array('title'=>'付款/收款人')
);

$menu=array(
'head'=>'<div class="right">'.
			$listLocator.
		'</div>'
);

$account_sum=array(
	'_field'=>array('总创收'),
	array(achievementSum('collected','total',option('date_range/from_timestamp'),option('date_range/to_timestamp'),false))
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$menu,true);
?>