<?php
class SS_Controller extends CI_Controller{
	
	var $controller;
	var $method;
	
	var $default_method='lists';

	var $view_data=array();//传递给视图的参数

	var $as_popup_window=false;
	var $as_controller_default_page=false;
	var $actual_table='';//借用数据表的controller的实际主读写表，如contact为client,query为cases
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
		
		//获得公司信息，见数据库，company表
		if($company_info=$this->company->fetch()){
			foreach($company_info as $config_name => $config_value){
				$this->config->set_item('company/'.$config_name, $config_value);
			}
		}
		if(is_file(APPPATH.'models/'.$this->config->item('company/type').'_model.php')){
			$this->load->model($this->config->item('company/type').'_model',$this->config->item('company/type'));
			$this->company_type_model_loaded=true;
		}
	
		if(is_file(APPPATH.'models/'.$class.'_model.php')){
			$this->load->model($class.'_model',$class);
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
		if($this->config->item('company/ucenter')){
			$this->load->helper('config_ucenter');
			require APPPATH.'third_party/client/client.php';
		}

		/**
		 * 弹出未登录用户
		 */
		if($class!='user' && !$this->user->isLogged(NULL,true)){
			redirect('user/login','js',NULL,true);
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
				if(is_posted('submit/file_document_list')){
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
	
	/**
	 * 在每个add/edit页面之前获得数据ID，插入新数据或者根据数据ID获得数据数组
	 * @param  $id	需要获得的数据id，如果是添加新数据，那么为NULL
	 * @param type $callback 对于新增数据，在执行插入操作之前进行一些赋值
	 * @param type $generate_new_id	如果$generate_new_id==false，那么必须在callback中获得post(CONTROLLER/id)，适用于id并非auto increasement，而是链接而来的情况
	 * @param type $db_table 实际操作的数据表名，默认为控制器名，否则须指定，如contact的表名为client
	 */
	function getPostData($id,$function_initializing_data=NULL,$generate_new_id=true,$db_table=NULL){
		if(isset($id)){
			unset($_SESSION[CONTROLLER]['post']);
			post(CONTROLLER.'/id',intval($id));
		
		}elseif(is_null(post(CONTROLLER.'/id'))){
			unset($_SESSION[CONTROLLER]['post']);
			
			post(CONTROLLER,uidTime());
				
			if(is_a($function_initializing_data,'Closure')){
				$CI=&get_instance();
				$function_initializing_data($CI);
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
		}
	
		if(!post(CONTROLLER.'/id')){
			show_error('获得信息ID失败');
			exit;
		}
		$class=CONTROLLER;
		post(CONTROLLER,$this->$class->fetch(post(CONTROLLER.'/id')));
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
			post(CONTROLLER.'/username',$_SESSION['username']);
		}

		post(CONTROLLER.'/company',$this->config->item('company/id'));

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

				if(is_posted('submit/'.CONTROLLER)){

					if(!$this->as_controller_default_page){
						unset($_SESSION[CONTROLLER]['post']);
					}

					if($this->as_popup_window){
						refreshParentContentFrame();
						closeWindow();
					}else{
						if($this->as_controller_default_page){
							showMessage('保存成功~');
						}else{
							redirect(($this->session->userdata('last_list_action')?$this->session->userdata('last_list_action'):CONTROLLER));
						}
					}
				}
			}
		}
	}

	function cancel(){
		unset($_SESSION[CONTROLLER]['post']);
		
		db_delete($this->actual_table==''?CONTROLLER:$this->actual_table,"uid={$this->user->id} AND display=0");//删除本用户的误添加数据
		
		if($this->as_popup_window){
			closeWindow();
		}else{
			redirect(($this->session->userdata('last_list_action')?$this->session->userdata('last_list_action'):CONTROLLER));
		}
	}

}
?>