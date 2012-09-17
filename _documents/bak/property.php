<?php
define('IN_UICE','property');
require 'config/config.php';

if(!is_logged())
	redirect('user.php?login','js',NULL,true);

if(got('view')){
	$_G['action']=IN_UICE.'_view';

}elseif(got('add')){
	$_G['action']=IN_UICE.'_add';

}elseif(got('addStatus')){
	$_G['action']=IN_UICE.'_addStatus';
}
elseif(!got('action')){
	$_G['action']=IN_UICE.'_list';

}else{
	exit('no permission');
}

require 'controller/export.php';
?>