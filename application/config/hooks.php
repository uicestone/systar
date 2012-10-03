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
	'class'=>NULL,
	'function'=>'preController',
	'filename'=>'pre_controller',
	'filepath'=>'hooks'
);

$hook['post_controller_constructor']=array(
	'class'=>NULL,
	'function'=>'postControllerConstructor',
	'filename'=>'post_controller_constructor',
	'filepath'=>'hooks'
);

$hook['post_controller']=array(
	'class'=>NULL,
	'function'=>'postController',
	'filename'=>'post_controller',
	'filepath'=>'hooks'
);

/* End of file hooks.php */
/* Location: ./application/config/hooks.php */