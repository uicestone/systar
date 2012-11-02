<?php
class SS_Controller extends CI_Controller{
	
	var $controller;
	var $method;
	
	var $default_method='lists';

	var $view_data=array();//传递给视图的参数

	var $as_popup_window=false;
	var $as_controller_default_page=false;
	var $actual_table='';//借用数据表的controller的实际主读写表，如contact为client,query为cases
	
	function __construct(){
		parent::__construct();
		
		global $class,$method;
		
		$this->load->helper('function_common');

		//使用controller中自定义的默认method
		if($method=='index'){
			$method=$this->default_method;
		}
		
		if($this->input->post('submit/cancel')){
			$this->load->require_head=false;
			$method='cancel';
		}
		
		//定义$class常量，即控制器的名称
		define('CONTROLLER',$class);
		define('METHOD',$method);
		
		$this->controller=$class;
		$this->method=$method;
		
		date_default_timezone_set('Asia/Shanghai');//定义时区，windows系统中php不能识别到系统时区
	
		session_set_cookie_params(86400); 
		session_start();
	
		//初始化数据库，本系统为了代码书写简便，没有将数据库操作作为类封装，但有大量实用函数在function/function_common.php->db_()
		$db['host']="localhost";
		$db['username']="root";
		$db['password']="";
		$db['name']='starsys';
	
		define('DB_LINK',mysql_connect($db['host'],$db['username'],$db['password']));
	
		mysql_select_db($db['name'],DB_LINK);

		$this->config->set_item('timestamp',time());
		$this->config->set_item('microtime',microtime(true));
		$this->config->set_item('date',date('Y-m-d',$this->config->item('timestamp')));
		$this->config->set_item('quarter',date('y',$this->config->item('timestamp')).ceil(date('m',$this->config->item('timestamp'))/3));
	
		db_query("SET NAMES 'UTF8'");
	
		//获得公司信息，见数据库，company表
		if($company_info=company_fetchInfo()){
			foreach($company_info as $config_name => $config_value){
				$this->config->set_item($config_name, $config_value);
			}
		}
	
		//ucenter配置
		if($this->config->item('ucenter')){
			$this->load->helper('config_ucenter');
			require APPPATH.'third_party/client/client.php';
		}

		if($class!='user' && !is_logged(NULL,true)){
			//对于非用户登录/登出界面，检查权限，弹出未登陆（顺便刷新权限）
			redirect('user/login','js',NULL,true);
		}

		//根据controller和method请求决定一些参数
		//这相当于集中处理了分散的控制器属性，在团队开发中，这不科学。有空应该把这些设置移动到对应的控制器中
		if(in_array($method,array('add','edit'))){
			$this->as_popup_window=TRUE;
		}
			
		if(in_array($class,array('frame','nav'))){
			$this->load->require_menu=false;
	
		}elseif($class=='cases'){
			$this->actual_table='case';
			if(($method=='add' || $method=='edit')){
				$this->as_popup_window=FALSE;
				if(is_posted('submit/file_document_list')){
					$this->load->require_head=false;
				}
				$this->as_popup_window=FALSE;
	
			}elseif($method=='write'){
				$this->load->require_head=false;
	
			}
		}elseif($class=='client'){
			if($method=='get_source_lawyer'){
				$this->load->require_head=false;
	
			}elseif($method=='autocomplete'){
				$this->load->require_head=false;
	
			}
		}elseif($class=='contact'){
			$this->actual_table='client';

		}elseif($class=='cron'){
			ignore_user_abort();
			set_time_limit(0);
			//error_reporting('~E_ALL');
	
			if($method=='script'){
				$this->action='cron_'.$_GET['script'];
	
			}
	
		}elseif($class=='document'){
			if(is_posted('fileSubmit')){
				$this->load->require_head=false;
	
			}elseif(is_posted('createDirSubmit')){
				$this->load->require_head=false;
	
			}elseif(is_posted('fav')){
				$this->load->require_head=false;
	
			}elseif(is_posted('favDelete')){
				$this->load->require_head=false;
	
			}elseif(($method=='view' || $method=='office_document' || $method=='instrument' || $method=='contact_file' || $method=='rules' || $method=='contract')){//根据目录ID进行定位/文件ID则进行下载
	
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
					$this->action="document_download";
					$this->load->require_head=false;
				}else{
					$_SESSION[$class]['upID']=$folder['parent'];
					$_SESSION[$class]['currentDir']=$folder['name'];
					$_SESSION[$class]['currentDirID']=$folder['id'];
					$_SESSION[$class]['currentPath']=$folder['path'];
				}
	
			}
		}elseif($class=='evaluation'){
			if($method=='score'){
				$this->as_popup_window=true;
	
			}elseif($method=='score_write'){
				$this->load->require_head=false;
	
			}
		}elseif($class=='misc'){
			$this->load->require_head=false;
	
		}elseif($class=='query'){
			$this->actual_table='case';
			$this->as_popup_window=FALSE;
			
	
		}elseif($class=='schedule'){
			if($method=='readcalendar'){
				$this->load->require_head=false;
	
			}elseif($method=='writecalendar'){
				$this->load->require_head=false;
	
			}elseif(($method=='list' || $method=='mine' || $method=='plan')){
				if(is_posted('export')){
					$this->load->require_head=false;
				}
	
			}elseif($method=='listwrite'){//日志列表写入评语/审核时间
				$this->load->require_head=false;
	
			}
		}elseif($class=='user'){
			if($method=='login'){
				$this->load->require_menu=false;
	
			}
		}elseif($class=='affair'){
			if($method=='switch'){
				$this->load->require_head=false;
	
			}
		}elseif($class=='exam'){
			if($method=='save'){
				$_G{'action'}=$class.'_list_save';
				$this->load->require_head=false;
	
			}
		}elseif($class=='student'){
			$this->as_popup_window=FALSE;
			if($method=='setclass'){
				$this->load->require_head=false;
	
			}elseif(is_logged('student')){
				post('student/id',$_SESSION['id']);
				$this->as_controller_default_page=true;
	
			}elseif(is_logged('parent')){
				post('student/id',$_SESSION['child']);
				$this->as_controller_default_page=true;
	
			}elseif(is_permitted($class)){//默认action
							}	
		}elseif($class=='survey'){
			if(got('action','homework')){
	
			}
		}elseif($class=='view_score'){
			if(is_posted('export_to_excel')){
				$this->load->require_head=false;
			}
		}

		$this->load->model('company_model','company');
		
		if(is_file(APPPATH.'models/'.$class.'_model.php')){
			$this->load->model($class.'_model',$class);
		}
	}
	
	/*
	 * 在每个add页面之前获得数据ID，插入新数据或者根据数据ID获得数据数组
	 */
	function getPostData($id,$callback=NULL,$generate_new_id=true,$db_table=NULL){
		if(isset($id)){
			unset($_SESSION[CONTROLLER]['post']);
			post(CONTROLLER.'/id',intval($id));
		
		}elseif(is_null(post(CONTROLLER.'/id'))){
			unset($_SESSION[CONTROLLER]['post']);
		
			$this->processUidTimeInfo(CONTROLLER);
		
			if(is_a($callback,'Closure')){
				$CI=&get_instance();
				$callback($CI);
			}
	
			if($generate_new_id){
				if(is_null($db_table)){
					if($this->actual_table!=''){
						$db_table=$this->actual_table;
					}else{
						$db_table=CONTROLLER;
					}
				}
				post(CONTROLLER.'/id',db_insert($db_table,post(CONTROLLER)));
			}
			//如果$generate_new_id==false，那么必须在callback中获得post(CONTROLLER/id)
		}
	
		if(!post(CONTROLLER.'/id')){
			showMessage('获得信息ID失败','warning');
			exit;
		}
		global $class;
		post(CONTROLLER,$this->$class->fetch(post(CONTROLLER.'/id')));
	}


	/*
	 * 为查询语句加上日期条件
	 */
	function dateRange(&$q,$date_field,$date_field_is_timestamp=true){
		if(is_posted('date_range_cancel')){
			unset($_SESSION[CONTROLLER][METHOD]['in_date_range']);
			unset($_SESSION[CONTROLLER][METHOD]['date_range']);
		}

		if(is_posted('date_range')){
			if(!strtotime($_POST['date_from']) || !strtotime($_POST['date_to'])){
				showMessage('日期格式错误','warning');

			}else{
				option('date_range/from_timestamp',strtotime($_POST['date_from']));
				option('date_range/to_timestamp',strtotime($_POST['date_to'])+86400);

				option('date_range/from',date('Y-m-d',option('date_range/from_timestamp')));
				option('date_range/to',date('Y-m-d',option('date_range/to_timestamp')-86400));

				option('in_date_range',true);
			}
		}

		if(option('in_date_range')){

			if($date_field_is_timestamp){
			$condition_date_range=" AND (".db_field_name($date_field).">='".option('date_range/from_timestamp')."' AND ".db_field_name($date_field)."<'".option('date_range/to_timestamp')."')";
			}else{
				$condition_date_range=" AND (".db_field_name($date_field).">='".option('date_range/from')."' AND ".db_field_name($date_field)."<='".option('date_range/to')."')";
			}

			$q.=$condition_date_range;
		}

		$date_range_bar=
		'<form method="post" name="date_range">'.
			'<table class="contentTable search-bar" cellpadding="0" cellspacing="0" align="center">'.
			'<thead><tr><td width="60px">日期</td><td>&nbsp;</td></tr></thead>'.
			'<tbody>'.
			'<tr><td>开始：</td><td><input type="text" name="date_from" value="'.option('date_range/from').'" class="date" /></td></tr>'.
			'<tr><td>结束：</td><td><input type="text" name="date_to" value="'.option('date_range/to').'" class="date" /></td></tr>'.
			'<input style="display:none;" name="date_field" value="'.$date_field.'" />';

		$date_range_bar.='<tr><td colspan="2"><input type="submit" name="date_range" value="提交" />';
		if(option('in_date_range')){
			$date_range_bar.='<input type="submit" name="date_range_cancel" value="取消" tabindex="1" />';
		}
		$date_range_bar.='</td></tr></tbody></table></form>';

		return $date_range_bar;
	}

	/*
	 * TODO 添加addCondition()的描述
	 */
	function addCondition(&$q,$condition_array,$unset=array()){
		foreach($unset as $changed_variable => $unset_variable){
			if(is_posted($changed_variable)){
				unset($_SESSION[CONTROLLER][METHOD][$unset_variable]);
			}
		}

		foreach($condition_array as $variable=>$field){
			if(is_posted($variable)){
				option($variable,$_POST[$variable]);
			}

			if(!is_null(option($variable)) && option($variable)!=''){
				$q.=' AND '.db_field_name($field)."='".option($variable)."'";
			}
		}
		return $q;
	}

	function processMultiPage(&$q,$q_rows=NULL){
		if(is_null($q_rows)){
			$q_rows=$q;
			if(preg_match('/GROUP BY[^()]*?[ORDER BY].*?$/',$q_rows)){
				$q_rows="SELECT COUNT(*) AS number FROM (".$q_rows.")query";
			}else{
				$q_rows=preg_replace('/^[\s\S]*?FROM /','SELECT COUNT(1) AS number FROM ',$q_rows);
				$q_rows=preg_replace('/GROUP BY(?![\s\S]*?WHERE)[\s\S]*?$/','',$q_rows);
				$q_rows=preg_replace('/ORDER BY(?![\s\S]*?WHERE)[\s\S]*?$/','',$q_rows);
			}
		}

		$rows=db_fetch_field($q_rows);

		if(option('list/start')>$rows || $rows==0){
			//已越界或空列表时，列表起点归零
			option('list/start',0);

		}elseif(option('list/start')+option('list/item')>=$rows && $rows>option('list/items')){
			//末页且非唯一页时，列表起点定位末页起点
			option('list/start',$rows - ($rows % option('list/items')));
		}

		if(!is_null(option('list/start')) && option('list/items')){
			if(is_posted('previousPage')){
				option('list/start',option('list/start')-option('list/items'));
				if(option('list/start')<0){
					option('list/start',0);
				}
			}elseif(is_posted('nextPage')){
				if(option('list/start')+option('list/items')<$rows){
					option('list/start',option('list/start')+option('list/items'));
				}
			}elseif(is_posted('firstPage')){
				option('list/start',0);
			}elseif(is_posted('finalPage')){
				if($rows % option('list/items')==0){
					option('list/start',$rows - option('list/items'));
				}else{
					option('list/start',$rows - ($rows % option('list/items')));
				}
			}
		}else{
			option('list/start',0);
			option('list/items',25);
		}

		$q.=" LIMIT ".option('list/start').",".option('list/items');

		$listLocator=($rows==0?0:option('list/start')+1)."-".
		(option('list/start')+option('list/items')<$rows?(option('list/start')+option('list/items')):$rows).'/'.$rows;

	}

	/* 
	 * $extra_action 是一个数组，接受除了返回列表/关闭窗口之外的其他提交后动作
	 * $after_update为数据库更新成功后，跳转前需要的额外操作
	 */
	function processSubmit($submitable,$after_update=NULL,$update_table=NULL,$set_display=true,$set_time=true,$set_user=true){
		if($set_display){
			post(CONTROLLER.'/display',1);
		}

		if($set_time){
			post(CONTROLLER.'/time',$this->config->item('timestamp'));
		}

		if($set_user){
			post(CONTROLLER.'/uid',$_SESSION['id']);
			post(CONTROLLER.'/username',$_SESSION['username']);
		}

		post(CONTROLLER.'/company',$this->config->item('company'));

		if(is_null($update_table)){
			if($this->actual_table!=''){
				$update_table=$this->actual_table;
			}else{
				$update_table=CONTROLLER;
			}
		}

		if($submitable){
			if(db_update($update_table,post(CONTROLLER),"id='".post(CONTROLLER.'/id')."'")){

				if(is_a($after_update,'Closure')){
					$after_update();
				}

				if(is_posted('submit/'.CONTROLLER)){

					if(!$this->config->item('as_controller_default_page')){
						unset($_SESSION[CONTROLLER]['post']);
					}

					if($this->config->item('as_popup_window')){
						refreshParentContentFrame();
						closeWindow();
					}else{
						if($this->config->item('as_controller_default_page')){
							showMessage('保存成功~');
						}else{
							redirect((sessioned('last_list_action')?$_SESSION['last_list_action']:CONTROLLER));
						}
					}
				}
			}
		}
	}

	function processUidTimeInfo($affair){
		if(!post($affair)){
			post($affair,array());
		}
		post($affair,post($affair)+uidTime());
	}
	
	function cancel(){
		unset($_SESSION[CONTROLLER]['post']);
		
		db_delete($this->actual_table==''?CONTROLLER:$this->actual_table,"uid='".$_SESSION['id']."' AND display=0");//删除本用户的误添加数据
		
		if($this->as_popup_window){
			closeWindow();
		}else{
			redirect((sessioned('last_list_action')?$_SESSION['last_list_action']:CONTROLLER));
		}
	}

}
?>