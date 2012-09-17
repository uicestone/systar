<?php
if($_G['require_export']){
	if(in_array(IN_UICE,array('index','nav'))){
		require 'view/common/head_'.IN_UICE.'.htm';
	}else{
		require "view/common/head.htm";
	}
	
	if($_G['require_menu']){
		require "view/common/menu.htm";
	}
}

if(file_exists('controller/'.$_G['action'].'.php')){
	require 'controller/'.$_G['action'].'.php';
}

if($_G['require_export'] && file_exists('view/'.$_G['action'].'.htm')){
	require 'view/'.$_G['action'].'.htm';
}

if(file_exists('view/'.$_G['action'].'_sidebar.htm')){
	echo '<div id="toolBar" '.(array_dir('_SESSION/minimized')?'class="minimized"':'').'>'.
		'<span class="minimize-button">-</span>';
	require 'view/'.$_G['action'].'_sidebar.htm';
	echo '</div>';
}

if($_G['require_export']){
	/*if(!in_array(IN_UICE,array('index','nav'))){
		showMessage($_G['db_execute_time']);
		showMessage($_G['db_executions']);
	}*/
	require 'view/common/foot.htm';
}

db_insert('log',array(
	'uri'=>$_SERVER['REQUEST_URI'],
	'host'=>$_SERVER['HTTP_HOST'],
	'get'=>serialize($_GET),
	'post'=>serialize($_POST),
	'client'=>$_SERVER['HTTP_USER_AGENT'],
	'duration'=>microtime(true)-$_G['microtime'],
	'ip'=>getIp(),'company'=>$_G['company_name'],
	'username'=>array_dir('_SESSION/username'),
	'time'=>date('Y-m-d H:i:s',$_G['timestamp']))
);
?>