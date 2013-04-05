<?php
/**
 * 一个分层的配置存取方式
 * 系统(脚本)<公司(数据库)<用户(数据库)<公司(数据库，控制器方法)<用户(数据库，控制器方法)<session(控制器方法)<post
 */
class SS_Config extends CI_Config{
	
	var $company;
	var $user;
	var $session;
	var $post;
	
	function __construct() {
		parent::__construct();
	}
	
	function user_item($item){

		if(isset($this->post[$item])){
			return $this->post[$item];
		}
		
		if(isset($this->session[CONTROLLER.'/'.METHOD.'/'.$item])){
			return $this->session[CONTROLLER.'/'.METHOD.'/'.$item];
		}
		
		if(isset($this->user[CONTROLLER.'/'.METHOD.'/'.$item])){
			return $this->user[CONTROLLER.'/'.METHOD.'/'.$item];
		}
		
		if(isset($this->company[CONTROLLER.'/'.METHOD.'/'.$item])){
			return $this->company[CONTROLLER.'/'.METHOD.'/'.$item];
		}
		
		if(isset($this->user[$item])){
			return $this->user[$item];
		}
		
		if(isset($this->company[$item])){
			return $this->company[$item];
		}
		
		return false;
	}
	
	/**
	 * 释放session中的一项配置
	 * @param $item 配置名或路径
	 * @param $level 配置作用范围method, controller, global
	 */
	function unset_user_item($item,$level='method'){
		$CI=&get_instance();
		$prefix='';
		if($level==='method'){
			$prefix.=CONTROLLER.'/'.METHOD.'/';
		}elseif($level==='controller'){
			$prefix.=CONTROLLER.'/';
		}
		unset($this->session[$prefix.$item]);
		$CI->session->unset_userdata('config/'.$prefix.$item);
	}
	
	/**
	 * @param $item
	 * @param $value
	 * @param $session 是否在session中改变设置
	 * @param $level 配置项的作用范围method, controller, global
	 */
	function set_user_item($item,$value,$session=true,$level='method'){
		
		if($session){
			$CI=&get_instance();
			$prefix='';
			if($level==='method'){
				$prefix.=CONTROLLER.'/'.METHOD.'/';
			}elseif($level==='controller'){
				$prefix.=CONTROLLER.'/';
			}
			
			//及时更新一次session的本地映射，否则更新的session要在下次请求才能被读取
			$this->session[$prefix.$item]=$value;
			
			$CI->session->set_userdata('config/'.$prefix.$item,$value);
		}
	}
}

?>
