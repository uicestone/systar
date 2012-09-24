<?php
require "function/function_common.php";
require "function/function_table.php";

date_default_timezone_set('Asia/Shanghai');

model(IN_UICE);

session_set_cookie_params(86400); 

session_start();

$db_host="localhost";
$db_username="starsys";
$db_password="!@!*xinghan";

$db_link=mysql_connect($db_host,$db_username,$db_password);
mysql_select_db('starsys',$db_link);

$_G['action']='';
$_G['timestamp']=time();
$_G['microtime']=microtime(true);
$_G['date']=date('Y-m-d',$_G['timestamp']);
$_G['quarter']=date('y',$_G['timestamp']).ceil(date('m',$_G['timestamp'])/3);
$_G['require_export']=true;//页面头尾输出开关（含menu）
$_G['require_menu']=true;//顶部蓝条/菜单输出开关
$_G['as_popup_window']=false;
$_G['as_controller_default_page']=false;
$_G['actual_table']='';//借用数据表的controller的实际主读写表，如contact为client,query为case
$_G['document_root']="D:/files";//文件系统根目录物理位置
$_G['case_document_path']="D:/case_document";//案下文件物理位置
$_G['db_execute_time']=0;
$_G['db_executions']=0;
$_G['debug_mode']=false;

db_query("SET NAMES 'UTF8'");

$_G+=db_fetch_first("SELECT id AS company,name AS company_name,type AS company_type,syscode,sysname,ucenter,default_controller FROM company WHERE host='".$_SERVER['HTTP_HOST']."' OR syscode='".$_SERVER['HTTP_HOST']."'");

//ucenter配置
if($_G['ucenter']){
	require 'config/config_ucenter.php';
	//if(IN_UICE!='uc_api'){
		require 'plugin/client/client.php';
	//}
}
?>