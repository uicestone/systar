<?php
/**
 * 一个分层的配置存取方式
 * 系统(脚本)<公司(数据库)<用户(数据库)<公司(数据库，控制器方法)<用户(数据库，控制器方法)<session(控制器方法)<post
 */
class SS_Config extends CI_Config{
	
	var $company;
	var $user;
	var $session;
	
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * 这里要兼容两种数据保存方式：多维数组和键名是路径的一维数组
	 * 如array('a/b'=>1)或array('a'=>array('b'=>1))
	 * @param type $item
	 * @return boolean
	 */
	function user_item($item, $method=NULL, $controller=NULL){
		
		is_null($controller) && $controller=CONTROLLER;
		is_null($method) && $method=METHOD;
		
		$plain_config = array_merge($this->company,$this->user,$this->session);
		
		$method = array_prefix($plain_config, $controller.'/'.$method.'/'.$item);
		
		$controller = array_prefix($plain_config, $controller.'/'.$item);
		
		if($method!==array()){
			return $method;
		}
		
		if($controller!==array()){
			return $controller;
		}
		
		$global = array_prefix($plain_config, $item);
		
		if($global!==array()){
			return $global;
		}
		
		return false;
	}
	
	/**
	 * 释放session中的一项配置
	 * @param $item 配置名或路径
	 * @param $level 配置作用范围method, global
	 */
	function unset_user_item($item,$level='method'){
		$prefix='';
		if($level==='method'){
			$prefix.=CONTROLLER.'/'.METHOD.'/';
		}
		
		unset($this->session[$prefix.$item]);
		
		$CI=&get_instance();
		$CI->session->unset_userdata('config/'.$prefix.$item);
	}
	
	/**
	 * @param $item
	 * @param $value
	 * @param $session 是否在session中改变设置
	 * @param $level 配置项的作用范围method,  global
	 * @param $override 当配置已存在时是否覆盖
	 */
	function set_user_item($item,$value,$session=true,$level='method',$override=true){
		
		$prefix='';
		if($level==='method'){
			$prefix.=CONTROLLER.'/'.METHOD.'/';
		}
			
		if($session){
			if($override || !array_key_exists($prefix.$item, $this->session)){
				$this->session[$prefix.$item]=$value;

				$CI=&get_instance();
				//及时更新一次session的本地映射，否则更新的session要在下次请求才能被读取

				$CI->session->set_userdata('config/'.$prefix.$item,$value);
			}
		}
		else{
			if($override || !array_key_exists($prefix.$item, $this->user)){
				$this->user[$prefix.$item]=$value;
			}
		}
	}
}

?>
