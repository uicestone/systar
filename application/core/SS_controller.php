<?php
class SS_Controller extends CI_Controller{
	
	/**
	 * 当前调用的控制器和方法
	 * @var type 
	 */
	var $controller;
	var $method;
	
	var $default_method='index';

	//var $as_popup_window=false;争取这一个属性也不用了！
	//var $as_controller_default_page=false;这个也是
	
	/**
	 * 当前控制器是否需要检查权限，只能在控制器构造函数中，父构造函数调用之前使用——因为现在的权限校验是放在大控制器的构造函数里的
	 */
	var $require_permission_check=true;
	
	var $company_type_model_loaded=false;
	
	function __construct(){
		parent::__construct();
		
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
		$this->load->model('label_model','label');
		
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
		if($this->require_permission_check && !$this->user->isLogged()){
			$this->output->status='login_required';
			$this->_output();
			exit;
		}
		
		/**
		 * 屏蔽无权限用户
		 */
		if($this->require_permission_check && !$this->user->isPermitted($class)){
			$this->output->status='denied';
			$this->_output();
			exit;
		}

		if($this->input->post('submit')=='date_range'){
			if(!strtotime($this->input->post('date_from')) || !strtotime($this->input->post('date_to'))){
				$this->output->message('日期格式错误','warning');

			}else{
				option('date_range/from_timestamp',strtotime($this->input->post('date_from')));
				option('date_range/to_timestamp',strtotime($this->input->post('date_to'))+86400);

				option('date_range/from',date('Y-m-d',option('date_range/from_timestamp')));
				option('date_range/to',date('Y-m-d',option('date_range/to_timestamp')-86400));

				option('in_date_range',true);
			}
		}

		if($this->input->post('submit')=='date_range_cancel'){
			unset($_SESSION[CONTROLLER][METHOD]['in_date_range']);
			unset($_SESSION[CONTROLLER][METHOD]['date_range']);
		}

		if($this->input->post('submit')=='search'){
			option('keyword',array_trim($this->input->post('keyword')));
			option('in_search_mod',true);
		}

		if($this->input->post('submit')=='search_cancel'){
			unset($_SESSION[CONTROLLER][METHOD]['in_search_mod']);
			unset($_SESSION[CONTROLLER][METHOD]['keyword']);
		}
		
		if(isset($this->user->permission[$this->controller][$this->method]['_affair_name'])){
			$this->output->setData($this->user->permission[$this->controller][$this->method]['_affair_name'], 'name');
		}elseif(isset($this->user->permission[$this->controller]['_affair_name'])){
			$this->output->setData($this->user->permission[$this->controller]['_affair_name'], 'name');
		}
		
		

	}
	
	/**
	 * 自定义的通用输出方法，系统不再直接将输出内容打印，而是传给此方法
	 * 此方法将当前Output类中的data,status,message等属性统一封装为json后输出
	 * 而且他还会判断当前页面是否包含一个传统html输出
	 * 如果是，那么追加一些需要执行的内嵌js代码，然后添加到Output::data中（这里需要用内嵌js是因为js的变量需要由后台程序赋值）
	 */
	function _output($output=''){
		
		if(!$this->output->as_ajax){
			echo $output;
			return;
		}
		
		header('Content-type: application/json');
		
		if($output){
			//如果在这个方法运行之前，页面就有输出，那么说明是一个旧式的输出html的页面，我们给它直接加上嵌入页面的js
			$output=$this->load->view('innerjs',array(),true).$output;
			$this->output->setData($output);
		}
		
		$sidebar=$this->load->sidebar_data.
			(is_file(APPPATH.'views/'.$this->controller.'/'.$this->method.'_sidebar'.EXT)?$this->load->view("{$this->controller}/{$this->method}_sidebar",array(),true):'');
		if($sidebar){
			$this->output->setData($sidebar,'sidebar');
		}
		
		$output_array=array(
			'status'=>$this->output->status,
			'message'=>$this->output->message,
			'data'=>$this->output->data
		);
		
		echo json_encode($output_array);
	}
	
	/**
	 * 在一个form中，用户修改任何input/select值时，就发送一个请求，保存到$_SESSION中
	 * 如此一来到发生保存请求时，只需要把$_SESSION中的新值保存即可
	 */
	function setFields($item_id){
		

		$controller=$this->controller;
		$this->$controller->id=$item_id;
		
		if(!is_array($this->input->post())){
			$this->output->status='fail';
			$this->output->message('invalid post data', 'warning');
			return;
		}
		foreach($this->input->post() as $field_name=>$value){
			post($field_name,$value);
		}
		$this->output->status='success';
	}
	
}
?>