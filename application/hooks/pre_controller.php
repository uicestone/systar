<?php
function preController(){
	require APPPATH."helpers/function_common.php";

	date_default_timezone_set('Asia/Shanghai');//定义时区，windows系统中php不能识别到系统时区

	session_set_cookie_params(86400); 

	session_start();

	//初始化数据库，本系统为了代码书写简便，没有将数据库操作作为类封装，但有大量实用函数在function/function_common.php->db_()
	$db['host']="localhost";
	$db['username']="root";
	$db['password']="";
	$db['name']='starsys';

	define('DB_LINK',mysql_connect($db['host'],$db['username'],$db['password']));

	mysql_select_db($db['name'],DB_LINK);

}
?>
