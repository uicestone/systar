<?php
define('IN_UICE','catologsale');
require 'config/config.php';

if(!is_logged())
	redirect('user.php?login','js',NULL,true);

if(is_posted('submit/cancel') && is_permitted(IN_UICE)){
	$_G['action']='misc_cancel';
	$_G['require_export']=false;
	
}elseif(is_posted('print')){
	$_G['action']=IN_UICE.'_print';
	$_G['require_export']=false;

}elseif(is_posted(IN_UICE.'Submit') && is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_insert';
	$_G['require_export']=false;

}elseif((got('add')||got('edit'))){
	$_G['action']=IN_UICE.'_add';

}elseif(is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_list';

}else{
	exit('no permission');
}

require 'controller/export.php';
?>