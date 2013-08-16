<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

if(php_sapi_name() === 'cli' OR defined('STDIN')){
	//cli 模式下不用考虑公司环境
	define('COMPANY_TYPE','');
	define('COMPANY_CODE','');
}
else{
	
	$companies=array(
		'lawfirm'=>array(
			'starsys'=>'sys.lawyerstars.com'
		),
		'school'=>array(
			'shdfz'=>'sdfz.sys.sh'
		),
	);
	
	//使用array_walk 匿名函数，不污染全局变量
	array_walk($companies,
		function($company,$company_type){
			array_walk($company,function($company_hostname,$company_code,$company_type){
				if($company_code===$_SERVER['HTTP_HOST'] || $company_hostname===$_SERVER['HTTP_HOST']){
					define('COMPANY_TYPE',$company_type);
					define('COMPANY_CODE',$company_code);
					return;
				}
				
				if(defined('COMPANY_TYPE') && defined('COMPANY_CODE')){
					return;
				}
			},$company_type);
		}
	);

	if(!defined('COMPANY_TYPE') || !defined('COMPANY_CODE')){
		show_error('unknown host '.$_SERVER['HTTP_HOST']);
	}
}

/* End of file constants.php */
/* Location: ./application/config/constants.php */