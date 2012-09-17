<?php
define('IN_UICE','user');
require 'config/config.php';

if(got('logout')){
	session_logout();
	redirect('user.php?login','js');

}elseif(got('login')){
	$_G['require_menu']=false;
	$_G['action']='user_login';

}elseif(got('profile')){
	$_G['action']='user_profile';
	
}

require 'controller/export.php';
?>