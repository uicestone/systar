<?php
class SS_Controller extends CI_Controller{
	
	var $controller;
	var $method;
	
	var $default_method='lists';

	/**
	 * 传递给视图的参数
	 */
	var $view_data=array();

	var $as_popup_window=false;
	var $as_controller_default_page=false;
	
	var $require_permission_check=true;
	
	/**
	 * 实际主读写表，如client为people,query为case
	 */
	var $actual_table;
	var $company_type_model_loaded=false;
	
	function __construct(){
		parent::__construct();
		
		/**
		 * 一些无法写入config.php的配置，要放在最首
		 */
		date_default_timezone_set('Asia/Shanghai');//定义时区，windows系统中php不能识别到系统时区
	
		session_set_cookie_params(86400); 
		session_start();
	
		$this->config->set_item('timestamp',time());
		$this->config->set_item('microtime',microtime(true));
		$this->config->set_item('date',date('Y-m-d',$this->config->item('timestamp')));
		$this->config->set_item('quarter',date('y',$this->config->item('timestamp')).ceil(date('m',$this->config->item('timestamp'))/3));
		
		/**
		 * 处理$class和$method，并定义为常量
		 */
		global $class,$method;
		
		//使用controller中自定义的默认method
		if($method=='index'){
			$method=$this->default_method;
		}
		
		//定义$class常量，即控制器的名称
		define('CONTROLLER',$class);
		define('METHOD',$method);
		
		$this->controller=$class;
		$this->method=$method;
		
		/**
		 * 自动载入的资源，没有使用autoload.php是因为后者载入以后不能起简称...
		 */
		$this->load->helper('function_common');
		$this->load->model('company_model','company');
		$this->load->model('user_model','user');
		
		if(is_file(APPPATH.'models/'.$class.'_model.php')){
			$this->load->model($class.'_model',$class);
		}

		if(is_file(APPPATH.'models/'.$this->company->type.'_model.php')){
			$this->load->model($this->company->type.'_model',$this->company->type);
			$this->company_type_model_loaded=true;
		}
	
		/**
		 * 初始化老版本数据库，老版本数据库调用方法全部废弃以后，删除本段
		 */
		$db['host']="localhost";
		$db['username']="root";
		$db['password']="";
		$db['name']='syssh';
	
		define('DB_LINK',mysql_connect($db['host'],$db['username'],$db['password']));
	
		mysql_select_db($db['name'],DB_LINK);

		db_query("SET NAMES 'UTF8'");
	
		/**
		 * ucenter配置
		 */
		if($this->company->ucenter){
			$this->load->helper('config_ucenter');
			require APPPATH.'third_party/client/client.php';
		}

		/**
		 * 弹出未登录用户
		 */
		if(!$this->user->isLogged(NULL) && $class!='user'){
			redirect('user/login','js',NULL,true);
		}
		
		/**
		 * 屏蔽无权限用户
		 */
		if($this->require_permission_check && !$this->user->isPermitted($class)){
			show_error('权限不足，无法访问');
		}

		/**
		 * 根据controller和method请求决定一些参数
		 * 这相当于集中处理了分散的控制器属性，在团队开发中，这不科学。有空应该把这些设置移动到对应的控制器中
		 */
		if(in_array($method,array('add','edit'))){
			$this->as_popup_window=TRUE;
		}
			
		if(in_array($class,array('frame','nav'))){
			$this->load->require_menu=false;
	
		}elseif($class=='cases'){
			$this->actual_table='case';
			if(($method=='add' || $method=='edit')){
				$this->as_popup_window=FALSE;
				if($this->input->post('file_document_list')!==false){
					$this->load->require_head=false;
				}
	
			}elseif($method=='write'){
				$this->load->require_head=false;
	
			}
		}elseif($class=='classes'){
			if(($method=='add' || $method=='edit')){
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
				$this->action='cron_'.$this->input->get('script');
	
			}
	
		}elseif($class=='document'){
			if($this->input->post('fileSubmit')){
				$this->load->require_head=false;
	
			}elseif($this->input->post('createDirSubmit')){
				$this->load->require_head=false;
	
			}elseif($this->input->post('fav')){
				$this->load->require_head=false;
	
			}elseif($this->input->post('favDelete')){
				$this->load->require_head=false;
	
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
			$this->as_popup_window=FALSE;
			
	
		}elseif($class=='schedule'){
			if($method=='readcalendar'){
				$this->load->require_head=false;
	
			}elseif($method=='writecalendar'){
				$this->load->require_head=false;
	
			}elseif(($method=='list' || $method=='mine' || $method=='plan')){
				if($this->input->post('export')){
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
		}elseif($class=='student'){
			$this->as_popup_window=FALSE;
		}

		if($this->input->post('submit/cancel')){
			$this->load->require_head=false;
			$method='cancel';
		}
		
		if($this->input->post('date_range')){
			if(!strtotime($this->input->post('date_from')) || !strtotime($this->input->post('date_to'))){
				showMessage('日期格式错误','warning');

			}else{
				option('date_range/from_timestamp',strtotime($this->input->post('date_from')));
				option('date_range/to_timestamp',strtotime($this->input->post('date_to'))+86400);

				option('date_range/from',date('Y-m-d',option('date_range/from_timestamp')));
				option('date_range/to',date('Y-m-d',option('date_range/to_timestamp')-86400));

				option('in_date_range',true);
			}
		}

		if($this->input->post('date_range_cancel')){
			unset($_SESSION[CONTROLLER][METHOD]['in_date_range']);
			unset($_SESSION[CONTROLLER][METHOD]['date_range']);
		}

		if($this->input->post('search')){
			option('keyword',array_trim($this->input->post('keyword')));
			option('in_search_mod',true);
		}

		if($this->input->post('search_cancel')){
			unset($_SESSION[CONTROLLER][METHOD]['in_search_mod']);
			unset($_SESSION[CONTROLLER][METHOD]['keyword']);
		}

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
			post(CONTROLLER.'/uid',$this->user->id);
			post(CONTROLLER.'/username',$this->user->name);
		}

		post(CONTROLLER.'/company',$this->company->id);

		if(is_null($update_table)){
			if($this->actual_table!=''){
				$update_table=$this->actual_table;
			}else{
				$update_table=CONTROLLER;
			}
		}

		if($submitable){
			if($this->db->update($update_table,post(CONTROLLER),array('id'=>post(CONTROLLER.'/id')))){

				if(is_a($after_update,'Closure')){
					$after_update();
				}

				return true;
			}
		}
	}

	function submit($submit){
		if($submit=='cancel'){
			unset($_SESSION[CONTROLLER]['post']);
			$this->db->delete($this->actual_table==''?CONTROLLER:$this->actual_table,"uid = {$this->user->id} AND display = 0");//删除本用户的误添加数据
			return true;
		}
	}
	
	/**
	 * ajax响应页面，在一个form中，用户修改任何input/select值时，就发送一个请求，保存到$_SESSION中
	 * 到发生保存请求时，只需要把$_SESSION中的新值保存即可
	 */
	function setFields($item_id){
		$this->load->require_head=false;

		$controller=CONTROLLER;
		$this->$controller->id=$item_id;
		
		if(!is_array($this->input->post())){
			echo 'invalid post data';
			return;
		}
		foreach($this->input->post() as $field_name=>$value){
			post($field_name,$value);
		}
		echo 'success';
	}
	
}
?>