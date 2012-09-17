<?php
define('IN_UICE','court');
require 'config/config.php';

if(!is_logged())
	redirect('user.php?login','js',NULL,true);

if(is_posted('follow')){
	$_G['action']=IN_UICE.'_follow';

}elseif(is_posted('filter')){
	$_G['action']=IN_UICE.'_filter';
	
}elseif(is_posted('exclude')){
	$_G['action']=IN_UICE.'_exclude';
	
}elseif(is_posted('followall')){
	$_G['action']=IN_UICE.'_followall';
	
}elseif(!got('action')){
	$_G['action']=IN_UICE.'_list';

}else{
	exit('no permission');
}

require 'controller/export.php';
?>