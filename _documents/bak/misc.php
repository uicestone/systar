<?php
define('IN_UICE','misc');
require 'config/config.php';

if(!is_logged())
	redirect('user.php?login','js',NULL,true);

$_G['require_export']=false;

if(got('get_html')){
	$_G['action']=IN_UICE.'_get_html';

}elseif(got('echo_session')){
	$_G['action']=IN_UICE.'_echo_session';

}elseif(got('get_select_option')){
	$_G['action']=IN_UICE.'_get_select_option';
	
}elseif(got('editable')){
	$_G['action']=IN_UICE.'_editable';
	
}

require 'controller/export.php';
?>