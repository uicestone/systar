<?php
function preController(){
	global $CFG,$class,$method;
	
	require "function/function_common.php";
	require "function/function_table.php";

	date_default_timezone_set('Asia/Shanghai');//定义时区，windows系统中php不能识别到系统时区

	defined('IN_UICE') && model(IN_UICE);//默认加在当前控制器对应model，比如当前控制器如果是case，则case_fetch(),case_update()等相关读写函数会被自动加载
	model('company');//这个model比较特殊，是各类型企业的函数库，也包含企业信息的通用函数比如conpany_fetchinfo()

	session_set_cookie_params(86400); 

	session_start();

	$db['host']="localhost";
	$db['username']="root";
	$db['password']="1";
	$db['name']='starsys';

	define('DB_LINK',mysql_connect($db['host'],$db['username'],$db['password']));

	mysql_select_db($db['name'],DB_LINK);

	//初始化数据库，本系统为了代码书写简便，没有将数据库操作作为类封装，但有大量实用函数在function/function_common.php->db_()
	$CFG->set_item('timestamp',time());
	$CFG->set_item('microtime',microtime(true));
	$CFG->set_item('date',date('Y-m-d',$CFG->item('timestamp')));
	$CFG->set_item('quarter',date('y',$CFG->item('timestamp')).ceil(date('m',$CFG->item('timestamp'))/3));
	$CFG->set_item('require_export',true);//页面头尾输出开关（含menu）
	$CFG->set_item('require_menu',true);//顶部蓝条/菜单输出开关
	$CFG->set_item('as_popup_window',false);
	$CFG->set_item('as_controller_default_page',false);
	$CFG->set_item('actual_table','');//借用数据表的controller的实际主读写表，如contact为client,query为case
	$CFG->set_item('document_root',"D:/files");//文件系统根目录物理位置
	$CFG->set_item('case_document_path',"D:/case_document");//案下文件物理位置
	$CFG->set_item('debug_mode',true);
	//定义一些系统配置，$_G不是php内置的大变量，是自定义的，为了在函数中可以方便地通过global $_G来获得所有配置

	db_query("SET NAMES 'UTF8'");

	if($company_info=company_fetchInfo()){
		foreach($company_info as $config_name => $config_value){
			$CFG->set_item($config_name, $config_value);
		}
	}
	//获得公司信息，见数据库，company表

	//ucenter配置
	if($CFG->item('ucenter')){
		require 'config/config_ucenter.php';
		require 'plugin/client/client.php';
	}

	define('IN_UICE',$class);//定义$class常量，即控制器的名称

	if($class!='user' && !is_logged(NULL,true)){
		//对于非用户登录/登出界面，检查权限，弹出未登陆
		redirect('user/login','js',NULL,true);
	}

	//开始选择controller
	if(in_array($class,array('frame','nav'))){
		$CFG->set_item('require_menu',false);

	}elseif($class=='account'){
		if(($method=='add' || $method=='edit') && is_permitted($class,'add')){
			$CFG->set_item('as_popup_window',true);

		}
	}elseif($class=='case'){
		if(($method=='add' || $method=='edit') && is_permitted($class,'add')){
			if(is_posted('submit/file_document_list')){
				$CFG->set_item('require_export',false);
			}

		}elseif($method=='write' && is_permitted($class)){
			$CFG->set_item('require_export',false);

		}
	}elseif($class=='client'){
		if(($method=='add'||$method=='edit') && is_permitted($class)){
			$CFG->set_item('as_popup_window',true);

		}elseif($method=='get_source_lawyer' && is_permitted($class)){
			$CFG->set_item('require_export',false);

		}elseif($method=='autocomplete'){
			$CFG->set_item('require_export',false);

		}
	}elseif($class=='contact'){
		$CFG->set_item('actual_table','client');
		if(($method=='add'||$method=='edit') && is_permitted($class)){
			$CFG->set_item('as_popup_window',true);

		}
	}elseif($class=='cron'){
		ignore_user_abort();
		set_time_limit(0);
		//error_reporting('~E_ALL');

		if($method=='script'){
			$CFG->set_item('action','cron_'.$_GET['script']);

		}/*else{
			//imperfect uicestone 2012/8/6 定时任务，尚未处理
			$q_cron="SELECT name,cycle,nextrun,lastrun cron where 1=1";
			$r_cron=db_query($q_cron);
			while($cron=mysql_fetch_array($r_cron)){
				if($_G['timestamp'] > $cron['next_run']){
					db_query("UPDATE cron set next_run =".($_G['timestamp']+$cron['cycle'])." WHERE id=".$cron['id']);
				}
			}
		}*/

	}elseif($class=='document'){
		if(is_posted('fileSubmit') && is_permitted($class)){
			$CFG->set_item('require_export',false);

		}elseif(is_posted('createDirSubmit') && is_permitted($class)){
			$CFG->set_item('require_export',false);

		}elseif(is_posted('fav') && is_permitted($class)){
			$CFG->set_item('require_export',false);

		}elseif(is_posted('favDelete') && is_permitted($class)){
			$CFG->set_item('require_export',false);

		}elseif(($method=='view' || $method=='office_document' || $method=='instrument' || $method=='contact_file' || $method=='rules' || $method=='contract') && is_permitted($class)){//根据目录ID进行定位/文件ID则进行下载

			if($method=='office_document'){
				$_GET['view']=869;

			}elseif($method=='instrument'){
				$_GET['view']=870;

			}elseif($method=='contact_file'){
				$_GET['view']=872;

			}elseif($method=='rules'){
				$_GET['view']=874;

			}elseif($method=='contract'){
				$_GET['view']=873;
			}


			option('in_search_mod',false);

			$folder=db_fetch_first("SELECT * FROM `document` WHERE id='".intval($_GET['view'])."'");

			if($folder['type']!=''){
				$CFG->set_item('action',"document_download");
				$CFG->set_item('require_export',false);
			}else{
				$_SESSION[$class]['upID']=$folder['parent'];
				$_SESSION[$class]['currentDir']=$folder['name'];
				$_SESSION[$class]['currentDirID']=$folder['id'];
				$_SESSION[$class]['currentPath']=$folder['path'];
			}

		}
	}elseif($class=='evaluation'){
		if($method=='score' && is_permitted($class)){
			$CFG->set_item('as_popup_window',true);

		}elseif($method=='score_write' && is_permitted($class)){
			$CFG->set_item('require_export',false);

		}
	}elseif($class=='file'){
		if(!$method=='action'){
			$_SESSION['last_list_action']='file';
		}
	}elseif($class=='misc'){
		$CFG->set_item('require_export',false);

		//包含model下所有库
		$handle = opendir('model');
		while($filename=readdir($handle)){
			if($filename!='.' && $filename!='..' && $filename!='company.php' && preg_match('/.*?\.php$/',$filename)){
				require('model/'.$filename);
			}
		}


	}elseif($class=='news'){
		if(($method=='add' || $method=='edit') && is_permitted($class,'add')){
			$CFG->set_item('as_popup_window',true);

		}
	}elseif($class=='query'){
		$CFG->set_item('actual_table','case');

	}elseif($class=='schedule'){
		if(($method=='add'||$method=='edit') && is_permitted($class)){
			$CFG->set_item('as_popup_window',true);

		}elseif($method=='readcalendar' && is_permitted($class)){
			$CFG->set_item('require_export',false);

		}elseif($method=='writecalendar' && is_permitted($class)){
			$CFG->set_item('require_export',false);

		}elseif(($method=='list' || $method=='mine' || $method=='plan') && is_permitted($class)){
			if(is_posted('export')){
				$CFG->set_item('require_export',false);
			}

		}elseif($method=='listwrite' && is_permitted($class)){//日志列表写入评语/审核时间
			$CFG->set_item('require_export',false);

		}
	}elseif($class=='user'){
		if($method=='login'){
			$CFG->set_item('require_menu',false);

		}
	}elseif($class=='affair'){
		if($method=='switch' && is_permitted('affair')){
			$CFG->set_item('require_export',false);

		}
	}elseif($class=='exam'){
		if($method=='save' && is_permitted($class)){
			$_G{'action'}=$class.'_list_save';
			$CFG->set_item('require_export',false);

		}
	}elseif($class=='student'){
		if($method=='setclass' && is_permitted($class)){
			$CFG->set_item('require_export',false);

		}elseif(is_logged('student') && is_permitted($class)){
			//学生查看/编辑自己的信息
			post('student/id',$_SESSION['id']);
			$CFG->set_item('as_controller_default_page',true);

		}elseif(is_logged('parent') && is_permitted($class)){
			//家长查看/编辑孩子的信息
			post('student/id',$_SESSION['child']);
			$CFG->set_item('as_controller_default_page',true);

		}elseif(is_permitted($class)){//默认action
						}	
	}elseif($class=='survey'){
		if(got('action','homework') && is_permitted('survey','homework')){

		}
	}elseif($class=='teach'){
		if(($method=='add'||$method=='edit') && is_permitted($class)){
			$CFG->set_item('as_popup_window',true);
		}

	}elseif($class=='view_score'){
		if(is_posted('export_to_excel')){
			$CFG->set_item('require_export',false);
		}
	}

	if(is_posted('submit/cancel') && is_permitted($class)){
		$CFG->set_item('require_export',false);
		$class='misc';
		$method='cancel';
	}
}
?>
