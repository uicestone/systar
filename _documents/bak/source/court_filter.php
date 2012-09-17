<?php
if(!defined('IN_UICE'))
	exit('no permission');
	
$q="UPDATE `court` SET status=1 
	WHERE ((length(plaintiff)<=9 AND length(defendant)<=9)
		OR matter in ('开庭信息筛选列表','离婚纠纷','离婚后财产纠纷','离婚后损害责任纠纷','婚姻家庭纠纷','婚姻无效纠纷','机动车交通事故责任纠纷','道路交通事故人身损害赔偿纠纷','法定继承纠纷','继承纠纷','遗嘱继承纠纷','遗赠纠纷','遗赠抚养协议纠纷','信用卡纠纷','分家析产纠纷','共有纠纷','共有物分割纠纷','共有权确认纠纷','相邻关系纠纷','赡养纠纷','赡养费纠纷','抚养纠纷','抚养费纠纷','同居关系子女抚养纠纷','变更抚养关系纠纷','行政其他','行政公安其他','行政城建其他','所有权纠纷','所有权确认纠纷','保险合同纠纷','财产保险纠纷','人身保险合同纠纷','责任保险合同纠纷','保险人代位求偿权纠纷','海上、通海水域保险合同纠纷','工伤保险待遇纠纷','医疗服务合同纠纷','医疗损害责任纠纷','医疗保险待遇纠纷','生命权、健康权、身体权纠纷'))
		AND status=0";

mysql_query($q,$db_link);

$q="SELECT * FROM court WHERE (defendant LIKE '%公司%' OR plaintiff LIKE '%公司%') AND status=0";
$r=mysql_query($q,$db_link);
while($a=mysql_fetch_array($r)){
	$client=array('type'=>'开庭信息','character'=>'artificial','time'=>time());
	$client_catologsale=array('status'=>1,'case'=>$a['id'],'time'=>time());
	$all_company=true;
	$plaintiff_array=explode(',',$a['plaintiff']);
	foreach($plaintiff_array as $k => $plaintiff){
		if(preg_match('/.*公司/',$plaintiff)){
			 $client['name']=$plaintiff;
			 $client_catologsale['id']=db_insert('client',$client);
			 if($client_catologsale['id'])
			 	db_insert('client_catologsale',$client_catologsale);
		}elseif(strlen($plaintiff)>9){
			$all_company=false;
		}
	}

	$defendant_array=explode(',',$a['defendant']);
	foreach($defendant_array as $k => $defendant){
		if(preg_match('/.*公司/',$defendant)){
			 $client['name']=$defendant;
			 $client_catologsale['id']=db_insert('client',$client);
			 db_insert('client_catologsale',$client_catologsale);
		}elseif(strlen($defendant)>9){
			$all_company=false;
		}
	}
	if($all_company){
		$q="UPDATE court SET status = 2 WHERE id = '".$a['id']."'";
		mysql_query($q,$db_link);
	}
}

redirect($_SERVER['REQUEST_URI'],'js');
?>