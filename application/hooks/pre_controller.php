<?php
function preController(){
	global $_G;

	$_G['controller']=$this->uri->segment(1);

	define('IN_UICE',$_G['controller']);//定义IN_UICE常量，即控制器的名称

	if(IN_UICE!='user' && !is_logged(NULL,true)){
		//对于非用户登录/登出界面，检查权限，弹出未登陆
		redirect('user/login','js',NULL,true);
	}

	//开始选择controller
	if(in_array(IN_UICE,array('','nav'))){
		$_G['require_menu']=false;

	}elseif(IN_UICE=='account'){
		if(($this->uri->segment(2)=='add' || $this->uri->segment(2)=='edit') && is_permitted(IN_UICE,'add')){
			$_G['as_popup_window']=true;

		}
	}elseif(IN_UICE=='case'){
		if(($this->uri->segment(2)=='add' || $this->uri->segment(2)=='edit') && is_permitted(IN_UICE,'add')){
			if(is_posted('submit/file_document_list')){
				$_G['require_export']=false;
			}

		}elseif($this->uri->segment(2)=='write' && is_permitted(IN_UICE)){
			$_G['require_export']=false;

		}
	}elseif(IN_UICE=='client'){
		if(($this->uri->segment(2)=='add'||$this->uri->segment(2)=='edit') && is_permitted(IN_UICE)){
			$_G['as_popup_window']=true;

		}elseif($this->uri->segment(2)=='get_source_lawyer' && is_permitted(IN_UICE)){
			$_G['require_export']=false;

		}elseif($this->uri->segment(2)=='autocomplete'){
			$_G['require_export']=false;

		}
	}elseif(IN_UICE=='contact'){
		$_G['actual_table']='client';
		if(($this->uri->segment(2)=='add'||$this->uri->segment(2)=='edit') && is_permitted(IN_UICE)){
			$_G['as_popup_window']=true;

		}
	}elseif(IN_UICE=='cron'){
		ignore_user_abort();
		set_time_limit(0);
		//error_reporting('~E_ALL');

		if($this->uri->segment(2)=='script'){
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

	}elseif(IN_UICE=='document'){
		if(is_posted('fileSubmit') && is_permitted(IN_UICE)){
			$_G['require_export']=false;

		}elseif(is_posted('createDirSubmit') && is_permitted(IN_UICE)){
			$_G['require_export']=false;

		}elseif(is_posted('fav') && is_permitted(IN_UICE)){
			$_G['require_export']=false;

		}elseif(is_posted('favDelete') && is_permitted(IN_UICE)){
			$_G['require_export']=false;

		}elseif(($this->uri->segment(2)=='view' || $this->uri->segment(2)=='office_document' || $this->uri->segment(2)=='instrument' || $this->uri->segment(2)=='contact_file' || $this->uri->segment(2)=='rules' || $this->uri->segment(2)=='contract') && is_permitted(IN_UICE)){//根据目录ID进行定位/文件ID则进行下载

			if($this->uri->segment(2)=='office_document'){
				$_GET['view']=869;

			}elseif($this->uri->segment(2)=='instrument'){
				$_GET['view']=870;

			}elseif($this->uri->segment(2)=='contact_file'){
				$_GET['view']=872;

			}elseif($this->uri->segment(2)=='rules'){
				$_GET['view']=874;

			}elseif($this->uri->segment(2)=='contract'){
				$_GET['view']=873;
			}


			option('in_search_mod',false);

			$folder=db_fetch_first("SELECT * FROM `document` WHERE id='".intval($_GET['view'])."'");

			if($folder['type']!=''){
				$_G['action']="document_download";
				$_G['require_export']=false;
			}else{
				$_SESSION[IN_UICE]['upID']=$folder['parent'];
				$_SESSION[IN_UICE]['currentDir']=$folder['name'];
				$_SESSION[IN_UICE]['currentDirID']=$folder['id'];
				$_SESSION[IN_UICE]['currentPath']=$folder['path'];
			}

		}
	}elseif(IN_UICE=='evaluation'){
		if($this->uri->segment(2)=='score' && is_permitted(IN_UICE)){
			$_G['as_popup_window']=true;

		}elseif($this->uri->segment(2)=='score_write' && is_permitted(IN_UICE)){
			$_G['require_export']=false;

		}
	}elseif(IN_UICE=='file'){
		if(!$this->uri->segment(2)=='action'){
			$_SESSION['last_list_action']='file';
		}
	}elseif(IN_UICE=='misc'){
		$_G['require_export']=false;

		//包含model下所有库
		$handle = opendir('model');
		while($filename=readdir($handle)){
			if($filename!='.' && $filename!='..' && $filename!='company.php' && preg_match('/.*?\.php$/',$filename)){
				require('model/'.$filename);
			}
		}


	}elseif(IN_UICE=='news'){
		if(($this->uri->segment(2)=='add' || $this->uri->segment(2)=='edit') && is_permitted(IN_UICE,'add')){
			$_G['as_popup_window']=true;

		}
	}elseif(IN_UICE=='query'){
		$_G['actual_table']='case';

	}elseif(IN_UICE=='schedule'){
		if(($this->uri->segment(2)=='add'||$this->uri->segment(2)=='edit') && is_permitted(IN_UICE)){
			$_G['as_popup_window']=true;

		}elseif($this->uri->segment(2)=='readcalendar' && is_permitted(IN_UICE)){
			$_G['require_export']=false;

		}elseif($this->uri->segment(2)=='writecalendar' && is_permitted(IN_UICE)){
			$_G['require_export']=false;

		}elseif(($this->uri->segment(2)=='list' || $this->uri->segment(2)=='mine' || $this->uri->segment(2)=='plan') && is_permitted(IN_UICE)){
			if(is_posted('export')){
				$_G['require_export']=false;
			}

		}elseif($this->uri->segment(2)=='listwrite' && is_permitted(IN_UICE)){//日志列表写入评语/审核时间
			$_G['require_export']=false;

		}
	}elseif(IN_UICE=='user'){
		if($this->uri->segment(2)=='logout'){
			session_logout();
			redirect('user/login','js');

		}elseif($this->uri->segment(2)=='login'){
			$_G['require_menu']=false;

		}
	}elseif(IN_UICE=='affair'){
		if($this->uri->segment(2)=='switch' && is_permitted('affair')){
			$_G['require_export']=false;

		}
	}elseif(IN_UICE=='exam'){
		if($this->uri->segment(2)=='save' && is_permitted(IN_UICE)){
			$_G{'action'}=IN_UICE.'_list_save';
			$_G['require_export']=false;

		}
	}elseif(IN_UICE=='pingjiao'){
		if(!$this->uri->segment(2)=='action')
			$_GET['action']=IN_UICE.'';


	}elseif(IN_UICE=='student'){
		if($this->uri->segment(2)=='setclass' && is_permitted(IN_UICE)){
			$_G['require_export']=false;

		}elseif(is_logged('student') && is_permitted(IN_UICE)){
			//学生查看/编辑自己的信息
			post('student/id',$_SESSION['id']);
			$_G['as_controller_default_page']=true;

		}elseif(is_logged('parent') && is_permitted(IN_UICE)){
			//家长查看/编辑孩子的信息
			post('student/id',$_SESSION['child']);
			$_G['as_controller_default_page']=true;

		}elseif(is_permitted(IN_UICE)){//默认action
						}	
	}elseif(IN_UICE=='survey'){
		if(got('action','homework') && is_permitted('survey','homework')){
			$_SESSION['action']='survey_homework';

		}
	}elseif(IN_UICE=='teach'){
		if(($this->uri->segment(2)=='add'||$this->uri->segment(2)=='edit') && is_permitted(IN_UICE)){
			$_G['as_popup_window']=true;
		}

	}elseif(IN_UICE=='view_score'){
		if(is_posted('export_to_excel')){
			$_G['require_export']=false;
		}
	}

	if(is_posted('submit/cancel') && is_permitted(IN_UICE)){
		$_G['require_export']=false;
		redirect('misc/cancel');
	}
}
?>
