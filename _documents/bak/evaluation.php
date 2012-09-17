<?php
define('IN_UICE','evaluation');
require 'config/config.php';

if(!is_logged())
	redirect('user.php?login','js',NULL,true);

if(is_posted('submit/cancel') && is_permitted(IN_UICE)){
	$_G['action']='misc_cancel';
	$_G['require_export']=false;
	
}elseif(got('staff_list') && is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_staff_list';
	
}elseif(got('score') && is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_score';
	
}elseif(got('score_write') && is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_score_write';
	$_G['require_export']=false;
	
}elseif(is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_list';
	
}else{
	exit('no permission');
}

require 'controller/export.php';
?>