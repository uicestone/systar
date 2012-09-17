<?php
if(!defined('IN_UICE'))
	exit('no permission');
	
unset($_POST['catologsaleSubmit']);

$_SESSION['client']['post']=array_trim($_POST);
$_SESSION['client']['post']['uid']=$_SESSION['id'];
$_SESSION['client']['post']['username']=$_SESSION['username'];
$_SESSION['client']['post']['time']=time();
$_SESSION['client']['post']['type']='开庭信息';
$_SESSION['client']['post']['character']='artificial';

$data_client_catologsale['status']=$_SESSION['client']['post']['status'];
$data_client_catologsale['case']=$_SESSION['client']['post']['case'];
$data_client_catologsale['time']=time();

unset($_SESSION['client']['post']['status']);
unset($_SESSION['client']['post']['case']);

if(got('add')){
	$q_client_check_duplicate="SELECT * FROM 
		client LEFT JOIN client_catologsale ON client.id=client_catologsale.id 
	WHERE client.name LIKE '".$_SESSION['client']['post']['name']."'";
	$r_client_check_duplicate=mysql_query($q_client_check_duplicate,$db_link);
	if($client_check_duplicate=mysql_fetch_array($r_client_check_duplicate) ){

		if(in_array($client_check_duplicate['status'],array(1,7))){
			db_update('client',$_SESSION['client']['post'],"id=".'client',"id=".$client_check_duplicate['id']);
			db_update('client_catologsale',$data_client_catologsale,"id=".$client_check_duplicate['id']);
		}
		
	}else{
		$data_client_catologsale['id']=db_insert('client',$_SESSION['client']['post']);
		db_insert('client_catologsale',$data_client_catologsale);
	}

}elseif(got('edit')){
	$client=$_GET['edit'];
	db_update('client',$_SESSION['client']['post'],"id='".$client."'");
	db_update('client_catologsale',$data_client_catologsale,"id='".$client."'");
	
}

unset($_SESSION[constant('IN_UICE')]['post']);

redirect(constant('IN_UICE').'.php');
?>