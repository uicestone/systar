<?php
$_G['controllers']=array('index','nav','user','misc','cron','account','achievement','case','class','client','contact','document','evaluation','express','file','news','property','query','schedule','affair','exam','pingjiao','pingjiao_admin','score','staff','student','studystatus','survey','teach','view_score');//合法的控制器

$_G['controller']=substr(preg_replace('/\.php$/','',$_SERVER['SCRIPT_NAME']),1);

if(in_array($_G['controller'],$_G['controllers'])){
	define('IN_UICE',$_G['controller']);
	chdir('../');

}else{
	exit('controller error');
}

require 'config/config.php';

if(IN_UICE!='user' && !is_logged(NULL,true)){
	//对于非用户登录/登出界面，检查权限，弹出未登陆
	redirect('user?login','js',NULL,true);
}

if(in_array(IN_UICE,array('index','nav'))){
	$_G['require_menu']=false;
	$_G['action']=IN_UICE;
	
}elseif(IN_UICE=='account'){
	if((got('add') || got('edit')) && is_permitted(IN_UICE,'add')){
		$_G['action']=IN_UICE.'_add';
		$_G['as_popup_window']=true;
	
	}elseif(got('case') && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_case_list';
	
	}elseif(is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_list';
	}
}elseif(IN_UICE=='achievement'){
	if(got('recent')){
		$_G['action']=IN_UICE.'_recent';
		
	}elseif(got('expired')){
		$_G['action']=IN_UICE.'_expired';
		
	}elseif(got('casebonus')){
		$_G['action']=IN_UICE.'_casebonus';
		
	}elseif(got('teambonus')){
		$_G['action']=IN_UICE.'_teambonus';
		
	}elseif(got('summary')){
		$_G['action']=IN_UICE.'_summary';
		
	}elseif(got('query')){
		$_G['action']=IN_UICE.'_query';
		
	}elseif(is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_list';
		
	}
}elseif(IN_UICE=='case'){
	if(got('document')){
		if(got('list')){
			$_G['action']=IN_UICE.'_document_list';
		}else{
			$_G['action']=IN_UICE.'_document_download';
			$_G['require_export']=false;
		}
		
	}elseif((got('add') || got('edit')) && is_permitted(IN_UICE,'add')){
		$_G['action']=IN_UICE.'_add';
	
	}elseif(got('review') && is_permitted(IN_UICE,'review')){
		$_G['action']=IN_UICE.'_review_list';
	
	}elseif(got('write') && is_permitted(IN_UICE)){
		$_G['require_export']=false;
		$_G['action']=IN_UICE.'_write';
	
	}elseif(is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_list';
	
	}
}elseif(IN_UICE=='class'){
	if((got('add')||got('edit')) && is_permitted(IN_UICE,'add')){
		$_G['action']=IN_UICE.'_add';
	
	}elseif(is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_list';
	
	}
}elseif(IN_UICE=='client'){
	if((got('add')||got('edit')) && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_add';
		$_G['as_popup_window']=true;
	
	}elseif(got('get_source_lawyer') && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_get_source_lawyer';
		$_G['require_export']=false;
	
	}elseif(got('autocomplete')){
		$_G['action']=IN_UICE.'_autocomplete';
		$_G['require_export']=false;
		
	}elseif(is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_list';
	
	}
}elseif(IN_UICE=='contact'){
	$_G['actual_table']='client';
	if((got('add')||got('edit')) && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_add';
		$_G['as_popup_window']=true;
	
	}elseif(is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_list';
	
	}
}elseif(IN_UICE=='cron'){
	ignore_user_abort();
	set_time_limit(0);
	//error_reporting('~E_ALL');
	
	if(got('script')){
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
		$_G['action']=IN_UICE.'_upload';
		$_G['require_export']=false;
	
	}elseif(is_posted('createDirSubmit') && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_createDir';
		$_G['require_export']=false;
	
	}elseif(is_posted('fav') && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_fav';
		$_G['require_export']=false;
	
	}elseif(is_posted('favDelete') && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_fav_delete';
		$_G['require_export']=false;
	
	}elseif((got('view') || got('office_document') || got('instrument') || got('contact_file') || got('rules') || got('contract')) && is_permitted(IN_UICE)){//根据目录ID进行定位/文件ID则进行下载
		
		if(got('office_document')){
			$_GET['view']=869;

		}elseif(got('instrument')){
			$_GET['view']=870;

		}elseif(got('contact_file')){
			$_GET['view']=872;

		}elseif(got('rules')){
			$_GET['view']=874;

		}elseif(got('contract')){
			$_GET['view']=873;
		}
		
		$_G['action']=IN_UICE.'_list';
		
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
	
	}elseif(is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_list';
	}
}elseif(IN_UICE=='evaluation'){
	if(got('staff_list') && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_staff_list';
		
	}elseif(got('score') && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_score';
		$_G['as_popup_window']=true;
		
	}elseif(got('result') && is_permitted(IN_UICE,'result')){
		$_G['action']=IN_UICE.'_result';
		
	}elseif(got('score_write') && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_score_write';
		$_G['require_export']=false;
		
	}elseif(is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_comment';
		
	}
}elseif(IN_UICE=='express'){
	if((got('add') || got('edit')) && is_permitted(IN_UICE,'add')){
		$_G['action']=IN_UICE.'_add';
	
	}elseif(is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_list';
	
	}
}elseif(IN_UICE=='file'){
	if(got('view')){
		$_G['action']=IN_UICE.'_view';
	
	}elseif(got('addStatus')){
		$_G['action']=IN_UICE.'_addStatus';
	
	}elseif(got('tobe')){
		$_G['action']=IN_UICE.'_tobe';
	
	}elseif(got('history')){
		$_G['action']=IN_UICE.'_history';
	
	}elseif(!got('action')){
		$_G['action']=IN_UICE.'_list';
		$_SESSION['last_list_action']='file';
	
	}
}elseif(IN_UICE=='misc'){
	$_G['require_export']=false;

	//包含model下所有库
	$handle = opendir('model');
	while($filename=readdir($handle)){
		if($filename!='.' && $filename!='..' && preg_match('/.*?\.php$/',$filename)){
			require('model/'.$filename);
		}
	}
	
	if(got('get_html')){
		$_G['action']=IN_UICE.'_get_html';
	
	}elseif(got('get_session')){
		$_G['action']=IN_UICE.'_get_session';
	
	}elseif(got('get_select_option')){
		$_G['action']=IN_UICE.'_get_select_option';
		
	}elseif(got('editable')){
		$_G['action']=IN_UICE.'_editable';
		
	}elseif(got('set_session')){
		$_G['action']=IN_UICE.'_set_session';
		
	}
}elseif(IN_UICE=='news'){
	if((got('add') || got('edit')) && is_permitted(IN_UICE,'add')){
		$_G['action']=IN_UICE.'_add';
		$_G['as_popup_window']=true;
	
	}elseif(is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_list';
	
	}
}elseif(IN_UICE=='property'){
	if(got('view')){
		$_G['action']=IN_UICE.'_view';
	
	}elseif(got('add')){
		$_G['action']=IN_UICE.'_add';
	
	}elseif(got('addStatus')){
		$_G['action']=IN_UICE.'_addStatus';
	}
	elseif(!got('action')){
		$_G['action']=IN_UICE.'_list';
	
	}
}elseif(IN_UICE=='query'){
	$_G['actual_table']='case';
	if((got('add') || got('edit')) && is_permitted(IN_UICE,'add')){
		$_G['action']=IN_UICE.'_add';
	
	}elseif(is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_list';
	
	}
}elseif(IN_UICE=='schedule'){
	if((got('add')||got('edit')) && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_add';
		$_G['as_popup_window']=true;
	
	}elseif(got('workhours') && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_workhours';
	
	}elseif(got('readcalendar') && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_readcalendar';
		$_G['require_export']=false;
		
	}elseif(got('writecalendar') && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_writecalendar';
		$_G['require_export']=false;
		
	}elseif((got('list') || got('mine') || got('plan')) && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_list';
		if(is_posted('export')){
			$_G['require_export']=false;
		}
		
	}elseif(got('outplan') && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_outplan';
		
	}elseif(got('listwrite') && is_permitted(IN_UICE)){//日志列表写入评语/审核时间
		$_G['action']=IN_UICE.'_list_write';
		$_G['require_export']=false;
	
	}elseif(is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_calendar';
	}
}elseif(IN_UICE=='user'){
	if(got('logout')){
		session_logout();
		redirect('user?login','js');
	
	}elseif(got('login')){
		$_G['require_menu']=false;
		$_G['action']=IN_UICE.'_login';
	
	}elseif(got('profile')){
		$_G['action']=IN_UICE.'_profile';

	}elseif(got('browser')){
		$_G['action']=IN_UICE.'_browser';
	}
}elseif(IN_UICE=='affair'){
	if(got('switch') && is_permitted('affair')){
		$_G['action']=IN_UICE.'_switch';
		$_G['require_export']=false;
		
	}elseif(is_permitted('affair')){
		$_G['action']=IN_UICE.'_list';
		
	}
}elseif(IN_UICE=='exam'){
	if(got('save') && is_permitted(IN_UICE)){
		$_G{'action'}=IN_UICE.'_list_save';
		$_G['require_export']=false;
	
	}elseif(got('exam') && got('view_seat') && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_view_seat';
	
	}elseif(got('exam') && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_paper_list';
	
	}elseif(got(IN_UICE.'paper') && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_part_list';
	
	}elseif(is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_list';
	
	}
}elseif(IN_UICE=='pingjiao'){
	if(!got('action'))
		$_GET['action']=IN_UICE.'';
	
	if(got('action','pingjiao') && is_permitted('pingjiao')){
		$_G['action']=IN_UICE.'_teacher_list';
	
	}elseif(got('action','score') && is_permitted('pingjiao')){
		$_G['action']=IN_UICE.'_score';
	
	}
}elseif(IN_UICE=='pingjiao_admin'){
	if(is_permitted('pingjiao',NULL,'admin')){
		
		if(got('action','suggest')){
			$_G['action']=IN_UICE.'_admin_suggest';
		}else
			$_G['action']=IN_UICE.'_admin';
	
	}
}elseif(IN_UICE=='score'){
	if(is_posted('partChooseSubmit') || !is_null(array_dir('_SESSION/score/currentExam'))){
		$_G['action']=IN_UICE.'_board';
		
	}elseif(is_permitted('score')){
		//$_G['action']=IN_UICE.'_partChoose';
		$_G['action']=IN_UICE.'_upload';
	
	}
}elseif(IN_UICE=='staff'){
	if(is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_list';
	}
		
}elseif(IN_UICE=='student'){
	if((got('add')||got('edit')) && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_add';
	
	}elseif(got('setclass') && is_permitted(IN_UICE)){
		$_G['require_export']=false;
		$_G['action']=IN_UICE.'_setclass';
		
	}elseif(got('viewscore') && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_viewscore';
		
	}elseif(got('classdiv') && is_permitted('teach')){
		$_G['action']=IN_UICE.'_classdiv';
	
	}elseif(got('interactive') && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_interactive';
	
	}elseif(is_logged('student') && is_permitted(IN_UICE)){
		//学生查看/编辑自己的信息
		post('student/id',$_SESSION['id']);
		$_G['as_controller_default_page']=true;
		$_G['action']=IN_UICE.'_add';
	
	}elseif(is_logged('parent') && is_permitted(IN_UICE)){
		//家长查看/编辑孩子的信息
		post('student/id',$_SESSION['child']);
		$_G['as_controller_default_page']=true;
		$_G['action']=IN_UICE.'_add';
	
	}elseif(is_permitted(IN_UICE)){//默认action
		$_G['action']=IN_UICE.'_list';
	}	
}elseif(IN_UICE=='survey'){
	if(got('action','homework') && is_permitted('survey','homework')){
	
		$_SESSION['action']='survey_homework';
	
	}
}elseif(IN_UICE=='teach'){
	if((got('add')||got('edit')) && is_permitted(IN_UICE)){
		$_G['action']=IN_UICE.'_add';
		$_G['as_popup_window']=true;
	
	}elseif(is_posted('addByClass') && is_permitted('teach')){
		$_G['action']=IN_UICE.'_addByClass';
		
	}elseif(is_posted('teachListSubmit') && is_permitted('teach')){
		$_G['action']=IN_UICE.'_list_submit';
	
	}elseif(is_posted('delete') && is_permitted('teach')){
		$_G['action']=IN_UICE.'_list_delete';
	
	}elseif(got('action','oneClass') && is_permitted('teach')){//got放在post后面
		$_G['action']=IN_UICE.'_list_oneClass';
	
	}elseif(is_permitted('teach')){
		$_G['action']=IN_UICE.'_list';
	}
		
}elseif(IN_UICE=='view_score'){
	if(is_posted('updateScore') || got('update')){
		$_G['action']=IN_UICE.'_update';
		
	}elseif(is_posted('export_to_excel')){
		$_G['action']=IN_UICE.'';
		$_G['require_export']=false;
	
	}elseif(got('by_type')){
		$_G['action']=IN_UICE.'_by_type';
	
	}elseif(got('rank_range')){
		$_G['action']=IN_UICE.'_rank_range';
	
	}elseif(is_permitted('score')){
		$_G['action']=IN_UICE;
	
	}
}else{
	exit('permission error');
}

if(is_posted('submit/cancel') && is_permitted(IN_UICE)){
	$_G['action']='misc_cancel';
	$_G['require_export']=false;
}

require 'controller/controller.php';
?>