<?php
if(!defined('IN_UICE'))
	exit('no permission');
	
if(got('add'))
	$action='addClient';
if(got('edit')){
	$action='editClient';$client=$_GET['edit'];
}
if(got('name')){
	array_dir('_SESSION/catologsale/post/name',$_GET['name']);
}

if(got('case')){
	array_dir('_SESSION/catologsale/post/case',$_GET['case']);
}

if($action=='editClient'){
	$q_client="SELECT 
			client.id,client.name,client.time,client.address,client.zipcode,
			client_catologsale.status,client_catologsale.case
		FROM 
			(
				SELECT * FROM client_catologsale WHERE client_catologsale.id='".$client."'
			)client_catologsale
			 LEFT JOIN client ON client.id=client_catologsale.id";
	$r_client=mysql_query($q_client,$db_link);
	array_dir('_SESSION/catologsale/post',db_fetch_array($r_client));
}

require 'html/catologsale_add.php';
?>