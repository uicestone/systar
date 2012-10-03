<?php
function preController(){
	global $_G,$class,$function;
	
	define('IN_UICE',$class);//定义$class常量，即控制器的名称

	if($class!='user' && !is_logged(NULL,true)){
		//对于非用户登录/登出界面，检查权限，弹出未登陆
		redirect('user/login','js',NULL,true);
	}

	//开始选择controller
	if(in_array($class,array('frame','nav'))){
		$_G['require_menu']=false;

	}elseif($class=='account'){
		if(($function=='add' || $function=='edit') && is_permitted($class,'add')){
			$_G['as_popup_window']=true;

		}
	}elseif($class=='case'){
		if(($function=='add' || $function=='edit') && is_permitted($class,'add')){
			if(is_posted('submit/file_document_list')){
				$_G['require_export']=false;
			}

		}elseif($function=='write' && is_permitted($class)){
			$_G['require_export']=false;

		}
	}elseif($class=='client'){
		if(($function=='add'||$function=='edit') && is_permitted($class)){
			$_G['as_popup_window']=true;

		}elseif($function=='get_source_lawyer' && is_permitted($class)){
			$_G['require_export']=false;

		}elseif($function=='autocomplete'){
			$_G['require_export']=false;

		}
	}elseif($class=='contact'){
		$_G['actual_table']='client';
		if(($function=='add'||$function=='edit') && is_permitted($class)){
			$_G['as_popup_window']=true;

		}
	}elseif($class=='cron'){
		ignore_user_abort();
		set_time_limit(0);
		//error_reporting('~E_ALL');

		if($function=='script'){
			$_G['action']='cron_'.$_GET['script'];

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
			$_G['require_export']=false;

		}elseif(is_posted('createDirSubmit') && is_permitted($class)){
			$_G['require_export']=false;

		}elseif(is_posted('fav') && is_permitted($class)){
			$_G['require_export']=false;

		}elseif(is_posted('favDelete') && is_permitted($class)){
			$_G['require_export']=false;

		}elseif(($function=='view' || $function=='office_document' || $function=='instrument' || $function=='contact_file' || $function=='rules' || $function=='contract') && is_permitted($class)){//根据目录ID进行定位/文件ID则进行下载

			if($function=='office_document'){
				$_GET['view']=869;

			}elseif($function=='instrument'){
				$_GET['view']=870;

			}elseif($function=='contact_file'){
				$_GET['view']=872;

			}elseif($function=='rules'){
				$_GET['view']=874;

			}elseif($function=='contract'){
				$_GET['view']=873;
			}


			option('in_search_mod',false);

			$folder=db_fetch_first("SELECT * FROM `document` WHERE id='".intval($_GET['view'])."'");

			if($folder['type']!=''){
				$_G['action']="document_download";
				$_G['require_export']=false;
			}else{
				$_SESSION[$class]['upID']=$folder['parent'];
				$_SESSION[$class]['currentDir']=$folder['name'];
				$_SESSION[$class]['currentDirID']=$folder['id'];
				$_SESSION[$class]['currentPath']=$folder['path'];
			}

		}
	}elseif($class=='evaluation'){
		if($function=='score' && is_permitted($class)){
			$_G['as_popup_window']=true;

		}elseif($function=='score_write' && is_permitted($class)){
			$_G['require_export']=false;

		}
	}elseif($class=='file'){
		if(!$function=='action'){
			$_SESSION['last_list_action']='file';
		}
	}elseif($class=='misc'){
		$_G['require_export']=false;

		//包含model下所有库
		$handle = opendir('model');
		while($filename=readdir($handle)){
			if($filename!='.' && $filename!='..' && $filename!='company.php' && preg_match('/.*?\.php$/',$filename)){
				require('model/'.$filename);
			}
		}


	}elseif($class=='news'){
		if(($function=='add' || $function=='edit') && is_permitted($class,'add')){
			$_G['as_popup_window']=true;

		}
	}elseif($class=='query'){
		$_G['actual_table']='case';

	}elseif($class=='schedule'){
		if(($function=='add'||$function=='edit') && is_permitted($class)){
			$_G['as_popup_window']=true;

		}elseif($function=='readcalendar' && is_permitted($class)){
			$_G['require_export']=false;

		}elseif($function=='writecalendar' && is_permitted($class)){
			$_G['require_export']=false;

		}elseif(($function=='list' || $function=='mine' || $function=='plan') && is_permitted($class)){
			if(is_posted('export')){
				$_G['require_export']=false;
			}

		}elseif($function=='listwrite' && is_permitted($class)){//日志列表写入评语/审核时间
			$_G['require_export']=false;

		}
	}elseif($class=='user'){
		if($function=='login'){
			$_G['require_menu']=false;

		}
	}elseif($class=='affair'){
		if($function=='switch' && is_permitted('affair')){
			$_G['require_export']=false;

		}
	}elseif($class=='exam'){
		if($function=='save' && is_permitted($class)){
			$_G{'action'}=$class.'_list_save';
			$_G['require_export']=false;

		}
	}elseif($class=='student'){
		if($function=='setclass' && is_permitted($class)){
			$_G['require_export']=false;

		}elseif(is_logged('student') && is_permitted($class)){
			//学生查看/编辑自己的信息
			post('student/id',$_SESSION['id']);
			$_G['as_controller_default_page']=true;

		}elseif(is_logged('parent') && is_permitted($class)){
			//家长查看/编辑孩子的信息
			post('student/id',$_SESSION['child']);
			$_G['as_controller_default_page']=true;

		}elseif(is_permitted($class)){//默认action
						}	
	}elseif($class=='survey'){
		if(got('action','homework') && is_permitted('survey','homework')){

		}
	}elseif($class=='teach'){
		if(($function=='add'||$function=='edit') && is_permitted($class)){
			$_G['as_popup_window']=true;
		}

	}elseif($class=='view_score'){
		if(is_posted('export_to_excel')){
			$_G['require_export']=false;
		}
	}

	if(is_posted('submit/cancel') && is_permitted($class)){
		$_G['require_export']=false;
		$class='misc';
		$function='cancel';
	}
}
?>
