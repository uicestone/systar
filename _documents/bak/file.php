<?php
define('IN_UICE','file');
require 'config/config.php';

if(!is_logged())
	redirect('user.php?login','js',NULL,true);

if(got('view')){
	$_G['action']=IN_UICE.'_view';

}elseif(got('add')){
	$_G['action']=IN_UICE.'_add';

}elseif(got('addStatus')){
	$_G['action']=IN_UICE.'_addStatus';

}elseif(got('tobe')){
	$_G['action']=IN_UICE.'_tobe';

}elseif(got('history')){
	$_G['action']=IN_UICE.'_history';

}elseif(!got('action')){
	$_G['action']=IN_UICE.'_list';
	$_SESSION['last_list_action']='file.php';

}else{
	exit('no permission');
}

require 'controller/export.php';
?>