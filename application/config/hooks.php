<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook['pre_controller']=array(
	'function'=>'preController',
	'filename'=>'pre_controller.php',
	'filepath'=>'hooks'
);

$hook['post_controller_constructor']=array(
	'function'=>'postControllerConstructor',
	'filename'=>'post_controller_constructor.php',
	'filepath'=>'hooks'
);

$hook['post_controller']=array(
	'function'=>'postController',
	'filename'=>'post_controller.php',
	'filepath'=>'hooks'
);

/* End of file hooks.php */
/* Location: ./application/config/hooks.php */