<?php
if(!defined('IN_UICE'))
	exit('no permission');
	
$_POST=array_trim($_POST);
if(isset($_POST['court'])){
	$condition = db_implode($_POST['court'], $glue = ' OR ','id','=',"'","'", '`','key');
	mysql_query("UPDATE court SET status='2' where (".$condition.')',$db_link);
}
redirect('/court');
?>