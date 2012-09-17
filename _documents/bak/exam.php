<?php
define('IN_UICE','exam');
require 'config/config.php';

if(!is_logged())
	redirect('user.php?login','js',NULL,true);

if(is_permitted(IN_UICE)){

	$_G['action']=IN_UICE.'_intern';

}else
	exit('no permission');

require 'controller/export.php';
?>