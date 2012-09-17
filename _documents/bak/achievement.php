<?php
define('IN_UICE','achievement');
require 'config/config.php';

if(!is_logged())
	redirect('user.php?login','js',NULL,true);

if(is_posted('submit/cancel') && is_permitted(IN_UICE)){
	$_G['action']='misc_cancel';
	$_G['require_export']=false;
	
}elseif(got('recent')){
	$_G['action']=IN_UICE.'_recent';
	
}elseif(got('expired')){
	$_G['action']=IN_UICE.'_expired';
	
}elseif(got('casebonus')){
	$_G['action']=IN_UICE.'_casebonus';
	
}elseif(got('teambonus')){
	$_G['action']=IN_UICE.'_teambonus';
	
}elseif(is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_list';
	
}else{
	exit('no permission');
}

require 'controller/export.php';
?>